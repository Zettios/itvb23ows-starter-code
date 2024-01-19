<?php
session_start();

include_once 'util.php';

$piece = $_POST['piece'];
$to = $_POST['to'];

$player = $_SESSION['player'];
$board = $_SESSION['board'];
$hand = $_SESSION['hand'][$player];

if (!$hand[$piece])
    $_SESSION['error'] = "Player does not have tile";
elseif (isset($board[$to]))
    $_SESSION['error'] = 'Board position is not empty';
elseif (count($board) && !hasNeighBour($to, $board))
    $_SESSION['error'] = "board position has no neighbour";
elseif (array_sum($hand) < 11 && !neighboursAreSameColor($player, $to, $board))
    $_SESSION['error'] = "Board position has opposing neighbour";
elseif (array_sum($hand) < 9 && $hand['Q'] >= 1 && $piece != "Q") {
    $_SESSION['error'] = 'Must play queen bee';
} else {
    $_SESSION['board'][$to] = [[$_SESSION['player'], $piece]];
    $_SESSION['hand'][$player][$piece]--;
    $_SESSION['player'] = 1 - $_SESSION['player'];

    $game_id = $_SESSION['game_id'];
    $type = "play";
    $last_move_id = $_SESSION['last_move'];

    $db = include 'database.php';
    $state = get_state();

    $stmt = $db->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssis', $game_id, $type, $piece, $to, $last_move_id, $state);
    $stmt->execute();
    $_SESSION['last_move'] = $db->insert_id;
}

header('Location: index.php');