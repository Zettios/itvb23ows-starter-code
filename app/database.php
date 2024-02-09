<?php

function connect_to_database() {
    return new mysqli('db', 'root', 'password1234', 'hive');
}

function get_state() {
    return serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
}

function set_state($result) {
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