<?php
class game_manager {
    private database $database;
    private hive_util $util;

    function __construct($database, $util) {
        $this->database = $database;
        $this->util = $util;
    }

    function init_game($db_connection) {
        $_SESSION['board'] = [];
        $_SESSION['hand'] = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
            1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0; //white
        $_SESSION['last_move'] = 0;
        $_SESSION['move_number'] = 0;
        $_SESSION['last_made_moves'] = [
            0 => [],
            1 => []
        ];
        $_SESSION['spider_moves'] = [];
        $_SESSION['ai_game'] = false;

        $db_connection->prepare('INSERT INTO games VALUES ()')->execute();
        $_SESSION['game_id'] = $db_connection->insert_id;
    }

    function get_playable_tiles($hand, $player): array {
        $playableTiles = [];
        foreach ($hand[$player] as $tile => $remainingPieces) {
            if ($remainingPieces != 0) {
                $playableTiles[] = $tile;
            }
        }
        return $playableTiles;
    }

    function get_play_and_move_positions($board, $player): array {
        $playPositions = [];
        $movePositions = [];

        $beetle = new beetle($this->util);
        $beetlePositions = [];

        $queenBee = new queenBee($this->util);
        $queenPositions = [];

        $spider = new spider($this->util);
        $spiderPositions = [];

        $antSoldier = new antSoldier($this->util);
        $antPositions = [];

        $grasshopper = new grasshopper($this->util);
        $grasshopperPositions = [];

        if (empty($board)) {
            $playPositions[] = '0,0';
        } else if (count($board) == 1) {
            $boardKeyArray = explode(',', array_key_first($board));
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $surroundingPosition = ($pq[0] + $boardKeyArray[0]).','.($pq[1] + $boardKeyArray[1]);
                $playPositions[] = $surroundingPosition;
            }
        } else {
            if (count($_SESSION['spider_moves']) >= 1) {
                $spiderCurrentLocation = $_SESSION['spider_moves'][count($_SESSION['spider_moves'])-1][1];
                $tile = array_pop($board[$spiderCurrentLocation]);
                $spiderMoveLocations = $spider->calculate_move_position($spiderCurrentLocation, $board);

                if (isset($board[$spiderCurrentLocation])) {
                    array_push($board[$spiderCurrentLocation], $tile);
                } else {
                    $board[$spiderCurrentLocation] = [$tile];
                }

                return [[], array_unique($spiderMoveLocations)];
            }

            foreach (array_keys($board) as $boardPosition) {
                if ($board[$boardPosition][count($board[$boardPosition])-1][0] == $player) {
                    $tile = array_pop($board[$boardPosition]);
                    $insectType = $tile[1];
                    switch ($insectType) {
                        case "Q":
                            $queenPositions = $queenBee->calculate_move_position($boardPosition, $board);
                            break;
                        case "B":
                            $beetlePositions = array_merge($beetlePositions, $beetle->calculate_move_position($boardPosition, $board));
                            break;
                        case "S":
                            $spiderPositions = array_merge($spiderPositions, $spider->calculate_move_position($boardPosition, $board));
                            break;
                        case "A":
                            $antPositions = array_merge($antPositions, $antSoldier->calculate_move_position($boardPosition, $board));
                            break;
                        case "G":
                            $grasshopperPositions = array_merge($grasshopperPositions, $grasshopper->calculate_move_position($boardPosition, $board));
                            break;
                    }

                    if (isset($board[$boardPosition])) {
                        array_push($board[$boardPosition], $tile);
                    } else {
                        $board[$boardPosition] = [$tile];
                    }

                    $boardPositionAsArray = explode(',', $boardPosition);
                    foreach ($GLOBALS['OFFSETS'] as $pq) {
                        $surroundingPosition = ($pq[0] + $boardPositionAsArray[0]).','.($pq[1] + $boardPositionAsArray[1]);
                        if (!array_key_exists($surroundingPosition, $board) && $this->util->neighbours_are_same_color_new($player, $surroundingPosition, $board)) {
                            $playPositions[] = $surroundingPosition;
                        }
                    }
                }
            }
        }

        $movePositions = array_merge($queenPositions,
                                        $beetlePositions,
                                        $spiderPositions,
                                        $antPositions,
                                        $grasshopperPositions);

        return [array_unique($playPositions), array_unique($movePositions)];
    }

    function play_insect($db_connection)
    {
        $piece = $_POST['piece'];
        $to = $_POST['to'];

        $player = $_SESSION['player'];
        $board = $_SESSION['board'];
        $hand = $_SESSION['hand'][$player];

        if ($_SESSION['ai_game'] && $player == 1) {
            $this->perform_play($player, $piece, $to, $db_connection);
        } else {
            if (!$hand[$piece]) {
                $_SESSION['error'] = "Player does not have tile";
            } elseif (isset($board[$to])) {
                $_SESSION['error'] = 'Board position is not empty';
            } elseif (count($board) && !$this->util->has_play_neighbour($to, $board)) {
                $_SESSION['error'] = "board position has no neighbour";
            } elseif (array_sum($hand) < 11 && !$this->util->neighbours_are_same_color_new($player, $to, $board)) {
                $_SESSION['error'] = "Board position has opposing neighbour";
            } elseif (array_sum($hand) < 9 && $hand['Q'] >= 1 && $piece != "Q") {
                $_SESSION['error'] = 'Must play queen bee';
            } else {
                $this->perform_play($player, $piece, $to, $db_connection);
            }
        }
    }

    function perform_play($player, $piece, $to, $db_connection) {
        $tileId = $this->util->generate_tile_id($player, $piece);
        $_SESSION['board'][$to] = [[$_SESSION['player'], $piece, $tileId]];
        $_SESSION['hand'][$player][$piece]--;
        $state = $this->util->get_game_state();
        $_SESSION['player'] = 1 - $_SESSION['player'];

        $_SESSION['last_move'] = $this->database->insert_player_move(
            $db_connection,
            "play",
            $_SESSION['game_id'],
            $piece, $to,
            $_SESSION['last_move'],
            $state);
        $_SESSION['move_number']++;
    }

    function move_insect($db_connection) {
        $from = $_POST['from'];
        $to = $_POST['to'];

        $player = $_SESSION['player'];
        $board = $_SESSION['board'];
        $hand = $_SESSION['hand'][$player];
        unset($_SESSION['error']);

        if ($_SESSION['ai_game'] && $player == 1) {
            $tile = array_pop($board[$from]);
            $_SESSION['board'] = $this->perform_move($board, $tile, $from, $to, $db_connection);
        } else {
            if (!isset($board[$from])) {
                $_SESSION['error'] = 'Board position is empty';
            } else if ($board[$from][count($board[$from])-1][0] != $player) {
                $_SESSION['error'] = "Tile is not owned by player";
            } else if ($hand['Q']) {
                $_SESSION['error'] = "Queen bee is not played";
            } else {
                $tile = array_pop($board[$from]);

                //Checks for a hive split
                if (!$this->util->has_move_neighbour($from, $to, $board)) {
                    $_SESSION['error'] = "Move would split hive";
                } else {
                    $all = array_keys($board);
                    $queue = [array_shift($all)];

                    while ($queue) {
                        $next = explode(',', array_shift($queue));
                        foreach ($GLOBALS['OFFSETS'] as $pq) {
                            list($p, $q) = $pq;

                            $p += $next[0];
                            $q += $next[1];

                            if (in_array("$p,$q", $all)) {
                                $queue[] = "$p,$q";
                                $all = array_diff($all, ["$p,$q"]);
                            }
                        }
                    }

                    if (!empty($all)) {
                        $_SESSION['error'] = "Move would split hive";
                    } else {
                        if ($from == $to) {
                            $_SESSION['error'] = 'Tile must move';
                        } elseif (isset($board[$to]) && $tile[1] != "B") {
                            $_SESSION['error'] = 'Tile not empty';
                        } elseif ($tile[1] != "G") {
                            if (!$this->util->can_tile_slide($board, $from, $to)) {
                                $_SESSION['error'] = 'Tile must slide';
                            }
                        }
                    }
                }

                if (isset($_SESSION['error'])) {
                    if (isset($board[$from])) {
                        array_push($board[$from], $tile);
                    } else {
                        $board[$from] = [$tile];
                    }
                } else {
                    $board = $this->perform_move($board, $tile, $from, $to, $db_connection);
                }
                $_SESSION['board'] = $board;
            }
        }
    }

    function perform_move($board, $tile, $from, $to, $db_connection): array {
        if (isset($board[$to])) {
            array_push($board[$to], $tile);
        } else {
            $board[$to] = [$tile];
        }

        if (empty($board[$from])) {
            unset($board[$from]);
        }

        $game_state = $this->util->get_game_state();

        $_SESSION['last_move'] = $this->database->insert_player_move(
            $db_connection,
            "move",
            $_SESSION['game_id'],
            $from, $to,
            $_SESSION['last_move'],
            $game_state);

        $_SESSION['move_number']++;

        if ($tile[1] == "S") {
            $_SESSION['spider_moves'][] = [$from, $to];
            if (count($_SESSION['spider_moves'] ) >= 3) {
                $_SESSION['spider_moves'] = [];
            }
        }

        $this->set_last_made_move($_SESSION['player'], $tile);

        if (empty($_SESSION['spider_moves'])) $_SESSION['player'] = 1 - $_SESSION['player'];
        return $board;
    }

    function set_last_made_move($player, $tile) {
        $_SESSION['last_made_moves'][$player][] = $tile[2];
        if (count($_SESSION['last_made_moves'][$player]) > 6) {
            array_shift($_SESSION['last_made_moves'][$player]);
        }
    }

    function check_for_win($board): array {
        $whiteWins = false;
        $blackWins = false;

        if (count($board) <= 2) {
            return ["", false];
        }

        foreach (array_keys($board) as $boardKey) {
            if ($board[$boardKey][0][1] == "Q") {
                $player = $board[$boardKey][0][0];
                $amountOfNeighbours = 0;
                $position = explode(',', $boardKey);
                foreach ($GLOBALS['OFFSETS'] as $pq) {
                    $surroundingPosition = ($pq[0] + $position[0]) . ',' . ($pq[1] + $position[1]);
                    if (array_key_exists($surroundingPosition, $board)) {
                        $amountOfNeighbours++;
                    }

                    if ($amountOfNeighbours >= 6) {
                        if ($player == 0) {
                            $blackWins = true;
                        }
                        if ($player == 1) {
                            $whiteWins = true;
                        }
                    }
                }
            }
        }

        if (!$whiteWins && !$blackWins) {
            if ($this->check_for_stalemate()) {
                $whiteWins = true;
                $blackWins = true;
            }
        }

        if ($whiteWins && $blackWins) {
            return["Gelijkspel!", true];
        } else if ($whiteWins) {
            return["Wit wint!", true];
        } else if ($blackWins) {
            return["Zwart wint!", true];
        } else {
            return ["", false];
        }
    }

    function check_for_stalemate(): bool {
        if (count($_SESSION['last_made_moves'][0]) <= 2 || count($_SESSION['last_made_moves'][1]) <= 2) {
            return false;
        }

        print_r($_SESSION['last_made_moves']);
        $prevWhiteId = $_SESSION['last_made_moves'][0][0];
        $whiteSameIdCounter = 0;
        $prevBlackId = $_SESSION['last_made_moves'][1][0];
        $blackSameIdCounter = 0;

        foreach (array_slice($_SESSION['last_made_moves'][0], 1) as $key => $val) {
            if ($val == $prevWhiteId) {
                $whiteSameIdCounter++;
            } else {
                $whiteSameIdCounter = 0;
            }
        }

        foreach (array_slice($_SESSION['last_made_moves'][1], 1) as $key => $val) {
            if ($val == $prevBlackId) {
                $blackSameIdCounter++;
            } else {
                $blackSameIdCounter = 0;
            }
        }

        if ($whiteSameIdCounter >= 5 && $blackSameIdCounter >= 5) {
            return true;
        }

        return false;
    }

    function must_player_pass_turn($playPositions, $movePositions): bool {
        if (empty($playPositions) && empty($movePositions)) {
            return true;
        }
        return false;
    }

    function pass_turn($db_connection) {
        $this->database->insert_player_pass($db_connection);
        $_SESSION['move_number']++;
        $_SESSION['last_move'] = $db_connection->insert_id;
        $_SESSION['player'] = 1 - $_SESSION['player'];
    }

    function undo_move($lastMove, $db_connection) {
        $result = $this->database->get_previous_move($db_connection, $lastMove);

        $_SESSION['last_move'] = $result[5];
        $this->util->set_game_state($result);

        $this->database->delete_previous_move($db_connection, $lastMove);
        $_SESSION['move_number']--;
    }
}