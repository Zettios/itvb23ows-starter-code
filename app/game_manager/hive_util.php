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
        if ($this->is_positive_diagonal_neighbour($from, $to)) return true;
        //if ($to[0] == $from[0] && abs($to[1] - $from[1]) == 1) return true;

        // Checks right & left
        if ($this->is_horizontal_neighbour($from, $to)) return true;
        //if ($to[1] == $from[1] && abs($to[0] - $from[0]) == 1) return true;


        if ($this->is_negative_diagonal_neighbour($from, $to)) return true;
//        if ($to[0] - $GLOBALS['OFFSETS'][4][0] == $from[0] && $to[1] - $GLOBALS['OFFSETS'][4][1] == $from[1]) return true;
//        if ($to[0] - $GLOBALS['OFFSETS'][5][0] == $from[0] && $to[1] - $GLOBALS['OFFSETS'][5][1] == $from[1]) return true;

        return false;
    }

    function is_positive_diagonal_neighbour($to, $from): bool {
        if ($to[0] == $from[0] && abs($to[1] - $from[1]) == 1) {
            return true;
        } else {
            return false;
        }
    }

    function is_horizontal_neighbour($to, $from): bool {
        if ($to[1] == $from[1] && abs($to[0] - $from[0]) == 1) {
            return true;
        } else {
            return false;
        }
    }

    function is_negative_diagonal_neighbour($to, $from): bool {
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

    // Bugged
//    function neighbours_are_same_color($player, $to, $board): bool {
//        foreach ($board as $boardKey => $boardValue) {
//            if (!$boardValue) continue;
//            $playerValue = $boardValue[count($boardValue) - 1][0];
//
//            if ($playerValue != $player && $this->is_neighbour($to, $boardKey)) return false;
//        }
//        return true;
//    }

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
}