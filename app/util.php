<?php

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];

//       0,-1      1,-1
//    -1,0     0,0    1,0
//       -1,1      0,1

function isNeighbour($a, $b) {
    $a = explode(',', $a);
    $b = explode(',', $b);
    if ($a[0] == $b[0] && abs($a[1] - $b[1]) == 1) return true;
    if ($a[1] == $b[1] && abs($a[0] - $b[0]) == 1) return true;
    if ($a[0] + $a[1] == $b[0] + $b[1]) return true;
    return false;
}

//  $a/To: 2,-2     $b/Key/From: 2,-1
//  2 == 2 && -2 - -1 == 1      //
//  -2 == -1 && 2 - 2 == 1      //
//  2 + -2 == 2 + -1            //


function hasNeighBour($to, $board) {
    foreach (array_keys($board) as $boardKey) {
        if (isNeighbour($to, $boardKey)) return true;
    }
}

function neighboursAreSameColor($player, $to, $board) {
    foreach ($board as $boardKey => $boardValue) {
        if (!$boardValue) continue;
        $playerValue = $boardValue[count($boardValue) - 1][0];

        if ($playerValue != $player && isNeighbour($to, $boardKey)) return false;
    }
    return true;
}

function len($tile) {
    return $tile ? count($tile) : 0;
}

function slide($board, $from, $to) {
    if (!hasNeighBour($to, $board)) return false;
    if (!isNeighbour($from, $to)) return false;
    $b = explode(',', $to);
    $common = [];
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $b[0] + $pq[0];
        $q = $b[1] + $pq[1];

        if (isNeighbour($from, $p.",".$q)) {
            $common[] = $p.",".$q;
        }
    }

    if (!$board[$common[0]] && !$board[$common[1]] && !$board[$from] && !$board[$to]) return false;
    return min(len($board[$common[0]]), len($board[$common[1]])) <= max(len($board[$from]), len($board[$to]));
}

?>