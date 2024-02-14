<?php
class hive_util {
    public function __construct() {
        $GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];
        //       0,-1      1,-1
        //    -1,0     0,0    1,0
        //       -1,1      0,1
    }

    function is_neighbour($to, $boardKey) {
        $to = explode(',', $to);
        $boardKey = explode(',', $boardKey);

        //  $To: 0,1 | $Key/From: 0,0
        //  0 == 0 && 1 - 0 == 1
        if ($to[0] == $boardKey[0] && abs($to[1] - $boardKey[1]) == 1) return true;
        //  1 == 0 && 0 - 0 == 1
        if ($to[1] == $boardKey[1] && abs($to[0] - $boardKey[0]) == 1) return true;
        //  0 + 1 == 0 + 0
        if ($to[0] + $to[1] == $boardKey[0] + $boardKey[1]) return true;
        return false;
    }

    function has_neighBour($to, $board) {
        foreach (array_keys($board) as $boardKey) {
            if ($this->is_neighbour($to, $boardKey)) return true;
        }
    }

    function neighbours_are_same_color($player, $to, $board) {
        foreach ($board as $boardKey => $boardValue) {
            if (!$boardValue) continue;
            $playerValue = $boardValue[count($boardValue) - 1][0];

            if ($playerValue != $player && $this->is_neighbour($to, $boardKey)) return false;
        }
        return true;
    }

    function len($tile) {
        return $tile ? count($tile) : 0;
    }

    function slide($board, $from, $to) {
        //Checks if the TO position has a neighbour
        if (!$this->has_neighBour($to, $board)) return false;
        //Checks if the FROM and TO positions are neighbours
        if (!$this->is_neighbour($from, $to)) return false;
        $b = explode(',', $to);
        $common = [];

        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];

            //Checks if P,Q is a neighbour to FROM
            if ($from !== $p.",".$q && $this->is_neighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }

        //if (!$board[$common[0]] && !$board[$common[1]] && !$board[$from] && !$board[$to]) return false;
        if (!array_key_exists($common[0], $board) && !array_key_exists($common[1], $board)
            && !array_key_exists($from, $board) && !array_key_exists($to, $board)) {
            return false;
        }

        return min(
                array_key_exists($common[0], $board) ? $this->len($board[$common[0]]) : 0,
                array_key_exists($common[1], $board) ? $this->len($board[$common[1]]) : 0) <=
            max(array_key_exists($from, $board) ? $this->len($board[$from]) : 0,
                array_key_exists($to, $board) ? $this->len($board[$to]) : 0);
    }
}