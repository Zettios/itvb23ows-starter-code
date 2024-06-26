<?php
class hive_util {
    public function __construct() {
        $GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];
        //       0,-1   1,-1
        //    -1,0   0,0   1,0
        //       -1,1   0,1
    }

    function is_neighbour($from, $to): bool {
        $from = explode(',', $from);
        $to = explode(',', $to);

        // Checks bottom right & top left
        if ($to[0] == $from[0] && abs($to[1] - $from[1]) == 1) {
            return true;
        }

        // Checks right & left
        if ($to[1] == $from[1] && abs($to[0] - $from[0]) == 1) {
            return true;
        }

        // Check the bottom left position
        if ($to[0] - $GLOBALS['OFFSETS'][4][0] == $from[0] && $to[1] - $GLOBALS['OFFSETS'][4][1] == $from[1]) {
            return true;
        }

        // Check the top right position
        if ($to[0] - $GLOBALS['OFFSETS'][5][0] == $from[0] && $to[1] - $GLOBALS['OFFSETS'][5][1] == $from[1]) {
            return true;
        }

        return false;
    }

    function has_move_neighbour($from, $to, $board): bool {
        $toPositionAsArray = explode(',', $to);
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $surroundingPosition = ($pq[0] + $toPositionAsArray[0]).','.($pq[1] + $toPositionAsArray[1]);
            if (isset($board[$from]) && count($board[$from]) >= 1) {
                if (array_key_exists($surroundingPosition, $board)) {
                    return true;
                }
            } else {
                if ($surroundingPosition != $from && array_key_exists($surroundingPosition, $board)) {
                    return true;
                }
            }
        }
        return false;
    }

    function has_play_neighbour($to, $board): bool {
        foreach (array_keys($board) as $boardKey) {
            if ($this->is_neighbour($to, $boardKey)) return true;
        }
        return false;
    }

    function neighbours_are_same_color_new($player, $from, $board): bool {
        $fromPositionAsArray = explode(',', $from);
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $surroundingPosition = ($pq[0] + $fromPositionAsArray[0]).','.($pq[1] + $fromPositionAsArray[1]);
            if (array_key_exists($surroundingPosition, $board)) {
                if ($board[$surroundingPosition][count($board[$surroundingPosition])-1][0] != $player) {
                    return false;
                }
            }
        }
        return true;
    }

    function len($tile): int {
        return $tile ? count($tile) : 0;
    }

    function can_tile_slide($board, $from, $to): bool {

        //Checks if the TO position has a neighbour
        if (!$this->has_move_neighbour($from, $to, $board)) return false;

        //Checks if the FROM and TO positions are neighbours
        if (!$this->is_neighbour($from, $to)) return false;


        $toAsArray = explode(',', $to);
        $common = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $toAsArray[0] + $pq[0];
            $q = $toAsArray[1] + $pq[1];

            //Checks if P,Q is a neighbour to FROM
            if ($from !== $p.",".$q && $this->is_neighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }

        if (!($board[$common[0]] ?? false) && !($board[$common[1]] ?? false) &&
            !($board[$from] ?? false) && !($board[$to] ?? false)) {
            return false;
        }

        $min1 = array_key_exists($common[0], $board) ? $this->len($board[$common[0]]) : 0;
        $min2 = array_key_exists($common[1], $board) ? $this->len($board[$common[1]]) : 0;
        $max1 = array_key_exists($from, $board) ? $this->len($board[$from]) : 0;
        $max2 = array_key_exists($to, $board) ? $this->len($board[$to]) : 0;

        return min($min1, $min2) <= max($max1, $max2);
    }

    function can_hop($board, $from, $to): bool {
        if ($this->is_neighbour($from, $to)) {
            return false;
        }

        $fromExplode = explode(',', $from);
        $validHop = false;
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $fromExplode[0] + $pq[0];
            $q = $fromExplode[1] + $pq[1];

            $tempP = $p;
            $tempQ = $q;


            $keepSearching = true;
            while ($keepSearching) {
                $tempP = $tempP + $pq[0];
                $tempQ = $tempQ + $pq[1];

                $key = $tempP.",".$tempQ;
                if (!array_key_exists($key, $board)) {
                    if ($key == $to) {
                        $validHop = true;
                    }
                    $keepSearching = false;
                }
            }
        }

        return $validHop;
    }

    function generate_tile_id($player, $piece): string {
        return $player.$piece.rand(1000000, 9999999);
    }

    function get_game_state(): string {
        return serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player'],
            $_SESSION['spider_moves'], $_SESSION['last_made_moves']]);
    }

    function set_game_state($result) {
        $type = $result[2];
        $moveFrom = $result[3];
        $moveTo = $result[4];
        $state = $result[6];
        list($hand, $board, $player, $_SESSION['spider_moves'], $_SESSION['last_made_moves']) = unserialize($state);
        if ($type == "play") {
            $hand[$player][$moveFrom]++;
            unset($board[$moveTo]);
        }

        $_SESSION['hand'] = $hand;
        $_SESSION['board'] = $board;
        $_SESSION['player'] = $player;
    }
}