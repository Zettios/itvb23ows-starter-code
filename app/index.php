<?php
    session_start();

    include_once 'game_manager/hive_util.php';
    include_once 'game_manager/game_manager.php';
    include_once 'database.php';

    include_once 'insects/insect.php';
    include_once 'insects/beetle.php';
    include_once 'insects/queenBee.php';
    include_once 'insects/spider.php';
    include_once 'insects/antSoldier.php';
    include_once 'insects/grasshopper.php';

    $util = new hive_util();
    $database = new database();
    $db_connection = $database->connect_to_database();
    $game_manager = new game_manager($database, $util);

    if (!isset($_SESSION['board']) || isset($_POST['restart'])) {
        $game_manager->init_game($db_connection);
    } else if (isset($_POST['play'])) {
        $game_manager->play_insect($db_connection);
    } else if (isset($_POST['move'])) {
        $game_manager->move_insect($db_connection);
    } else if (isset($_POST['pass'])) {
        $game_manager->pass_turn($db_connection);
    } else if (isset($_POST['undo'])) {
        $game_manager->undo_move($_SESSION['last_move'], $db_connection);
    }

    $lastMoveId = $_SESSION['last_move'];
    $game_id = $_SESSION['game_id'];
    $board = $_SESSION['board'];
    $player = $_SESSION['player'];
    $hand = $_SESSION['hand'];
    $movePositions = [];
    $playPositions = [];
    $gameOver = false;
    $mustPassTurn = false;

    if (count($board) > 2) {
        $winnerValues = $game_manager->check_for_win($board);
        if ($winnerValues[0] && $winnerValues[1]) {
            echo "Gelijkspel!";
            $gameOver = true;
        } else if ($winnerValues[0]) {
            echo "Wit wint!";
            $gameOver = true;
        } else if ($winnerValues[1]) {
            echo "Zwart wint!";
            $gameOver = true;
        }
    }

    $playableTiles = $game_manager->get_playable_tiles($hand, $player);

    $playAndMovePositions = $game_manager->get_play_and_move_positions($board, $player);
    $playPositions = $playAndMovePositions[0];
    $movePositions = $playAndMovePositions[1];
    if (count($board) > 2) {
        $mustPassTurn = $game_manager->must_player_pass_turn($playPositions, $movePositions);
    }

    $url = "http://hive-ai:5000";
    $content = json_encode("{
                                \"move_number\": 1
                                \"hand\": [
                                    {\"Q\": 0, \"B\": 2, \"A\": 3, \"S\": 3, \"G\": 3},
                                    {\"Q\": 1, \"B\": 2, \"A\": 3, \"S\": 3, \"G\": 3},
                                ],
                                \"board\": {
                                    \"0,0\": [[0, \"Q\"]],
                                }
                            }");

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

    $json_response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ( $status != 201 ) {
        die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
    }

    curl_close($curl);

    $response = json_decode($json_response, true);
    echo "<pre>";
    echo ($response);
    echo "</pre>";



?>
<!DOCTYPE html>
<html>
    <head>
        <title>Hive</title>
        <link rel="stylesheet" href="styling.css">
    </head>
    <body>
        <div class="board">
            <?php
                $min_p = 1000;
                $min_q = 1000;
                foreach ($board as $boardPosition => $tile) {
                    $pq = explode(',', $boardPosition);
                    if ($pq[0] < $min_p) $min_p = $pq[0];
                    if ($pq[1] < $min_q) $min_q = $pq[1];
                }
                foreach (array_filter($board) as $boardPosition => $tile) {
                    $pq = explode(',', $boardPosition);
                    $pq[0];
                    $pq[1];
                    $h = count($tile);
                    echo '<div class="tile player';
                    echo $tile[$h-1][0];
                    if ($h > 1) echo ' stacked';
                    echo '" style="left: ';
                    echo ($pq[0] - $min_p) * 4 + ($pq[1] - $min_q) * 2;
                    echo 'em; top: ';
                    echo ($pq[1] - $min_q) * 4;
                    echo "em;\">($pq[0],$pq[1])<span>";
                    echo $tile[$h-1][1];
                    echo '</span></div>';
                }
            ?>
        </div>
        <div class="hand">
            White:
            <?php
                foreach ($hand[0] as $tile => $remainingPieces) {
                    for ($i = 0; $i < $remainingPieces; $i++) {
                        echo '<div class="tile player0"><span>'.$tile."</span></div> ";
                    }
                }
            ?>
        </div>
        <div class="hand">
            Black:
            <?php
            foreach ($hand[1] as $tile => $remainingPieces) {
                for ($i = 0; $i < $remainingPieces; $i++) {
                    echo '<div class="tile player1"><span>'.$tile."</span></div> ";
                }
            }
            ?>
        </div>
        <div class="turn">
            Turn: <?php if ($player == 0) echo "White (0)"; else echo "Black (1)"; ?>
        </div>

        <!---------------- PLAY INSECT ---------------->
        <form method="post" action="index.php">
            <select name="piece" <?php
            if (array_sum($hand[$player]) <= '0' || count($_SESSION['spider_moves']) >= 1 || $gameOver || $mustPassTurn){ echo "disabled"; } ?>>
                <?php
                    foreach ($playableTiles as $key => $tile) {
                        echo "<option value=\"$tile\">$tile</option>";
                    }
                ?>
            </select>
            <select name="to" <?php
            if (array_sum($hand[$player]) <= '0' || count($_SESSION['spider_moves']) >= 1 || $gameOver || $mustPassTurn){ echo "disabled"; } ?>>
                <?php
                    foreach ($playPositions as $boardPosition) {
                        echo "<option value=\"$boardPosition\">$boardPosition</option>";
                    }
                ?>
            </select>
            <input type="submit" name="play" value="Play" <?php
            if (array_sum($hand[$player]) <= '0'  || count($_SESSION['spider_moves']) >= 1 || $gameOver || $mustPassTurn){ echo "disabled"; } ?>>
        </form>

        <!---------------- MOVE INSECT ---------------->
        <form method="post" action="index.php">
            <select name="from" <?php if ($hand[$player]["Q"] >= 1 || $gameOver || $mustPassTurn) { echo "disabled"; } ?>>
                <?php
                    if (count($_SESSION['spider_moves']) >= 1) {
                        $val = $_SESSION['spider_moves'][array_key_last($_SESSION['spider_moves'])][1];
                        echo "<option value=\"$val\">$val</option>";
                    } else {
                        foreach (array_keys($board) as $boardPosition) {
                            if ($board[$boardPosition][count($board[$boardPosition])-1][0] == $player) {
                                echo "<option value=\"$boardPosition\">$boardPosition</option>";
                            }
                        }
                    }
                ?>
            </select>
            <select name="to" <?php if ($hand[$player]["Q"] >= 1 || $gameOver || $mustPassTurn) { echo "disabled"; } ?>>
                <?php
                    foreach ($movePositions as $boardPosition) {
                        echo "<option value=\"$boardPosition\">$boardPosition</option>";
                    }
                ?>
            </select>
            <input type="submit" name="move" value="Move" <?php if ($hand[$player]["Q"] >= 1 || $gameOver || $mustPassTurn) { echo "disabled"; } ?>>
        </form>

        <!---------------- PASS ---------------->
        <form method="post" action="index.php">
            <input type="submit" name="pass" value="Pass" <?php if ($gameOver || !$mustPassTurn){ echo "disabled"; } ?>>
        </form>

        <!---------------- RESTART ---------------->
        <form method="post" action="index.php">
            <input type="submit" name="restart" value="Restart">
        </form>

        <!---------------- ERROR MESSAGE ---------------->
        <strong><?php if (isset($_SESSION['error'])) echo($_SESSION['error']); unset($_SESSION['error']); ?></strong>

        <!---------------- GAME HISTORY ---------------->
        <ol>
            <?php
                echo "Game id: " . $game_id . "<br>";
                $result = $database->get_game_history($db_connection, $game_id);
                while ($row = $result->fetch_array()) {
                    echo '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
                }
            ?>
        </ol>

        <!---------------- UNDO MOVE ---------------->
        <form method="post" action="index.php">
            <input type="submit" name="undo" value="Undo" <?php if ($lastMoveId <= '0'){ echo "disabled"; } ?>>
        </form>
    </body>
</html>

