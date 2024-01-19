<?php

function get_state() {
    return serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
}

function set_state($result) {
    $moveId = $result[0];
    $gameId = $result[1];
    $type = $result[2];
    $moveFrom = $result[3];
    $moveTo = $result[4];
    $prevId = $result[5];
    $state = $result[6];
    list($hand, $board, $player) = unserialize($state);

//    echo '<pre>';
//    echo print_r($hand);
//    echo print_r($board);
//    echo "<script>console.log($player)</script>";
//    echo '</pre>';

    if ($type == "play") {
        $player = abs($player - 1);
        $hand[$player][$moveFrom]++;
        unset($board[$moveTo]);
    }

    $_SESSION['hand'] = $hand;
    $_SESSION['board'] = $board;
    $_SESSION['player'] = $player;
}

return new mysqli('db', 'root', '', 'hive');

?>