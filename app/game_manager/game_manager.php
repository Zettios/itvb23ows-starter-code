<?php
class game_manager {
    private database $database;
    private hive_util $util;

    function __construct($database, $util) {
        $this->database = $database;
        $this->util = $util;
    }

    function init_game($db) {
        $_SESSION['board'] = [];
        $_SESSION['hand'] = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
            1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0; //white
        $_SESSION['last_move'] = 0;

        $db->prepare('INSERT INTO games VALUES ()')->execute();
        $_SESSION['game_id'] = $db->insert_id;
    }

    function get_play_and_move_positions($board, $player): array {
        $playPositions = [];
        $movePositions = [];

        if (empty($board)) {
            $playPositions[] = '0,0';
        } else if (count($board) == 1) {
            $boardKeyArray = explode(',', array_key_first($board));
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $surroundingPosition = ($pq[0] + $boardKeyArray[0]).','.($pq[1] + $boardKeyArray[1]);
                $playPositions[] = $surroundingPosition;
            }
        } else {
            foreach (array_keys($board) as $boardPosition) {
                if ($board[$boardPosition][count($board[$boardPosition])-1][0] == $player) {
                    $boardPositionAsArray = explode(',', $boardPosition);
                    foreach ($GLOBALS['OFFSETS'] as $pq) {
                        $surroundingPosition = ($pq[0] + $boardPositionAsArray[0]).','.($pq[1] + $boardPositionAsArray[1]);
                        if (!array_key_exists($surroundingPosition, $board) && $this->util->neighbours_are_same_color_new($player, $surroundingPosition, $board)) {
                            $playPositions[] = $surroundingPosition;
                        }

                        $movePositions[] = $surroundingPosition;
                        if (array_key_exists($surroundingPosition, $board) && $board[$boardPosition][count($board[$boardPosition])-1][1] != "B") {
                            array_pop($movePositions);
                        }
                    }
                }
            }
        }

        return [array_unique($playPositions), array_unique($movePositions)];
    }

    function play_insect($db_conn) {
        $piece = $_POST['piece'];
        $to = $_POST['to'];

        $player = $_SESSION['player'];
        $board = $_SESSION['board'];
        $hand = $_SESSION['hand'][$player];

        if (!$hand[$piece]) {
            $_SESSION['error'] = "Player does not have tile";
        } elseif (isset($board[$to])) {
            $_SESSION['error'] = 'Board position is not empty';
        } elseif (count($board) && !$this->util->has_neighBour($to, $board)) {
            $_SESSION['error'] = "board position has no neighbour";
        } elseif (array_sum($hand) < 11 && !$this->util->neighbours_are_same_color_new($player, $to, $board)) {
            $_SESSION['error'] = "Board position has opposing neighbour";
        } elseif (array_sum($hand) < 9 && $hand['Q'] >= 1 && $piece != "Q") {
            $_SESSION['error'] = 'Must play queen bee';
        } else {
            $_SESSION['board'][$to] = [[$_SESSION['player'], $piece]];
            $_SESSION['hand'][$player][$piece]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];

            $state = $this->get_game_state();

            $_SESSION['last_move'] = $this->database->insert_player_move(
                $db_conn,
                "play",
                $_SESSION['game_id'],
                $piece, $to,
                $_SESSION['last_move'],
                $state);
        }
    }

    function move_insect($db_conn) {
        $from = $_POST['from'];
        $to = $_POST['to'];

        $player = $_SESSION['player'];
        $board = $_SESSION['board'];
        $hand = $_SESSION['hand'][$player];
        unset($_SESSION['error']);

        if (!isset($board[$from]))
            $_SESSION['error'] = 'Board position is empty';
        elseif ($board[$from][count($board[$from])-1][0] != $player)
            $_SESSION['error'] = "Tile is not owned by player";
        elseif ($hand['Q'])
            $_SESSION['error'] = "Queen bee is not played";
        else {
            $tile = array_pop($board[$from]);

            if (!$this->util->has_neighBour($to, $board)) {
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
                    } elseif ($tile[1] == "Q" || $tile[1] == "B") {
                        if (!$this->util->slide($board, $from, $to)) {
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
                if (isset($board[$to])) {
                    array_push($board[$to], $tile);
                } else {
                    $board[$to] = [$tile];
                }

                if (empty($board[$from])) {
                    unset($board[$from]);
                }

                $_SESSION['player'] = 1 - $_SESSION['player'];

                $game_state = $this->get_game_state();

                $_SESSION['last_move'] = $this->database->insert_player_move(
                    $db_conn,
                    "move",
                    $_SESSION['game_id'],
                    $from, $to,
                    $_SESSION['last_move'],
                    $game_state);

            }
            $_SESSION['board'] = $board;
        }
    }

    function check_for_win(): bool {

        return false;
    }

    function pass_turn($db) {
        $stmt = $db->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $_SESSION['game_id'], $_SESSION['last_move'], $this->get_game_state());
        $stmt->execute();
        $_SESSION['last_move'] = $db->insert_id;
        $_SESSION['player'] = 1 - $_SESSION['player'];
    }

    function undo_move($lastMove, $db) {
        $stmt = $db->prepare('SELECT * FROM moves WHERE id = '.$lastMove);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();

        $_SESSION['last_move'] = $result[5];
        $this->set_game_state($result);

        $stmt = $db->prepare('DELETE FROM moves WHERE id = '.$lastMove);
        $stmt->execute();
    }

    function get_game_state(): string {
        return serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
    }

    function set_game_state($result) {
        $type = $result[2];
        $moveFrom = $result[3];
        $moveTo = $result[4];
        $state = $result[6];
        list($hand, $board, $player) = unserialize($state);

        $player = abs($player - 1);
        if ($type == "play") {
            $hand[$player][$moveFrom]++;
            unset($board[$moveTo]);
        }

        $_SESSION['hand'] = $hand;
        $_SESSION['board'] = $board;
        $_SESSION['player'] = $player;
    }
}