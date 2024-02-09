<?php
    session_start();

    include_once 'game_manager/util.php';
    include_once 'game_manager/game_manager.php';
    include 'database.php';

    $db = connect_to_database();

    if (!isset($_SESSION['board']) || isset($_POST['restart'])) {
        init_game($db);
    } else if (isset($_POST['play'])) {
        play_insect($db);
    } else if (isset($_POST['move'])) {
        move_insect_old($db);
    } else if (isset($_POST['pass'])) {
        pass_turn($db);
    } else if (isset($_POST['undo'])) {
        undo_move($_SESSION['last_move'], $db);
    }

    $lastMoveId = $_SESSION['last_move'];
    $game_id = $_SESSION['game_id'];
    $board = $_SESSION['board'];
    $player = $_SESSION['player'];
    $hand = $_SESSION['hand'];
    $to = [];
    $movePositions = [];
    $playPositions = [];

//    echo "<pre>";
//    print_r($board);
//    echo "</pre>";

//    foreach ($GLOBALS['OFFSETS'] as $pq) {
//        foreach (array_keys($board) as $pos) {
//            $pq2 = explode(',', $pos);
//            $position = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
//
//            if (count($board) >= 2) {
//                if ($board[$pos][count($board[$pos])-1][0] == $player) {
//                    $to[] = $position;
//                }
//            } else {
//                $to[] = $position;
//            }
//        }
//    }

    if (!empty($board)) {
        foreach (array_keys($board) as $pos) {
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $pq2 = explode(',', $pos);
                $position = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);


                if (count($board) >= 2) {
                    if ($board[$pos][count($board[$pos])-1][0] == $player) {
                        if (!array_key_exists($position, $board) && neighbours_are_same_color($player, $position, $board)) {
                            $to[] = $position;
                            $playPositions[] = $position;
                        }

                        $movePositions[] = $position;
                        if (array_key_exists($position, $board) && $board[$pos][count($board[$pos])-1][1] != "B") {
                            array_pop($movePositions);
                        }
                    }
                } else {
                    $to[] = $position;
                    $playPositions[] = $position;
                }
            }
        }
    } else {
        $to[] = '0,0';
        $playPositions[] = '0,0';
    }

    $to = array_unique($to);
    $playPositions = array_unique($playPositions);
    $movePositions = array_unique($movePositions);
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
                foreach ($board as $pos => $tile) {
                    $pq = explode(',', $pos);
                    if ($pq[0] < $min_p) $min_p = $pq[0];
                    if ($pq[1] < $min_q) $min_q = $pq[1];
                }
                foreach (array_filter($board) as $pos => $tile) {
                    $pq = explode(',', $pos);
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
            <select name="piece" <?php if (array_sum($hand[$player]) <= '0'){ echo "disabled"; } ?>>
                <?php
                    foreach ($hand[$player] as $tile => $remainingPieces) {
                        if ($remainingPieces != 0) {
                            echo "<option value=\"$tile\">$tile</option>";
                        }
                    }
                ?>
            </select>
            <select name="to" <?php if (array_sum($hand[$player]) <= '0'){ echo "disabled"; } ?>>
                <?php
                    foreach ($playPositions as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" name="play" value="Play" <?php if (array_sum($hand[$player]) <= '0'){ echo "disabled"; } ?>>
        </form>

        <!---------------- MOVE INSECT ---------------->
        <form method="post" action="index.php">
            <select name="from" <?php if ($hand[$player]["Q"] >= 1) { echo "disabled"; } ?>>
                <?php
                    foreach (array_keys($board) as $pos) {
                        if ($board[$pos][0][0] == $player) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    }
                ?>
            </select>
            <select name="to" <?php if ($hand[$player]["Q"] >= 1) { echo "disabled"; } ?>>
                <?php
                    foreach ($movePositions as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" name="move" value="Move" <?php if ($hand[$player]["Q"] >= 1) { echo "disabled"; } ?>>
        </form>

        <!---------------- PASS ---------------->
        <form method="post" action="index.php">
            <input type="submit" name="pass" value="Pass">
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
                $stmt = $db->prepare('SELECT * FROM moves WHERE game_id = '.$_SESSION['game_id']);
                $stmt->execute();
                $result = $stmt->get_result();
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

