<?php

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];
$GLOBALS['OFFSETS_NAMES'] = ["BOTTOM_RIGHT", "TOP_LEFT", "RIGHT", "LEFT", "BOTTOM_LEFT", "TOP_RIGHT"];

//       0,-1      1,-1
//    -1,0     0,0    1,0
//       -1,1      0,1

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
        if (is_neighbour($to, $boardKey)) return true;
    }
}

function neighbours_are_same_color($player, $to, $board) {
    foreach ($board as $boardKey => $boardValue) {
        if (!$boardValue) continue;
        $playerValue = $boardValue[count($boardValue) - 1][0];

        if ($playerValue != $player && is_neighbour($to, $boardKey)) return false;
    }
    return true;
}

function valid_play_positions($player, $pos, $board) {
    $pq = explode(',', $pos);
    echo "Position: ".$pos."<br>";
    echo "-----------------------<br>";
    echo "Surrounding values:<br>";
    foreach ($GLOBALS['OFFSETS'] as $offset) {
        $position = ($pq[0] + $offset[0]).','.($pq[1] + $offset[1]);
        echo $position."<br>";
        if (array_key_exists($position, $board)) {
            if ($board[$position][count($board[$position])-1][0] != $player) {
                return false;
            }
        }
    }
    echo "<br>";

    return true;
}

function len($tile) {
    return $tile ? count($tile) : 0;
}

function slide($board, $from, $to) {
    //Checks if the TO position has a neighbour
    if (!has_neighBour($to, $board)) return false;
    //Checks if the FROM and TO positions are neighbours
    if (!is_neighbour($from, $to)) return false;
    $b = explode(',', $to);
    $common = [];

    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $b[0] + $pq[0];
        $q = $b[1] + $pq[1];

        //Checks if P,Q is a neighbour to FROM
        if ($from !== $p.",".$q && is_neighbour($from, $p.",".$q)) {
            $common[] = $p.",".$q;
        }
    }

    //if (!$board[$common[0]] && !$board[$common[1]] && !$board[$from] && !$board[$to]) return false;
    if (!array_key_exists($common[0], $board) && !array_key_exists($common[1], $board)
        && !array_key_exists($from, $board) && !array_key_exists($to, $board)) {
        return false;
    }

    return min(
            array_key_exists($common[0], $board) ? len($board[$common[0]]) : 0,
            array_key_exists($common[1], $board) ? len($board[$common[1]]) : 0) <=
            max(array_key_exists($from, $board) ? len($board[$from]) : 0,
                array_key_exists($to, $board) ? len($board[$to]) : 0);
}

// =========== New functions (or attept at) ===========
function is_neighbour_new($to, $boardKey) {
    $to = explode(',', $to);
    $boardKey = explode(',', $boardKey);

    $val1 = $to[0] == $boardKey[0] && abs($to[1] - $boardKey[1]) == 1;
    $val2 = $to[1] == $boardKey[1] && abs($to[0] - $boardKey[0]) == 1;
    $val3 = $to[0] + $to[1] == $boardKey[0] + $boardKey[1];

    echo "Val1: ".$val1."<br>";
    echo "Val2: ".$val2."<br>";
    echo "Val3: ".$val3."<br>";

    if ($val1) return true;
    if ($val2) return true;
    if ($val3) return true;
    return false;
}

function can_slide($from, $to, $board) {
    return false;
}

function has_neighbour_new($from, $to, $board) {
    $b = explode(',', $to);
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $b[0] + $pq[0];
        $q = $b[1] + $pq[1];

        if ($from !== $p.",".$q && array_key_exists($p.",".$q, $board)) {
            return true;
        }
    }

    return false;
}