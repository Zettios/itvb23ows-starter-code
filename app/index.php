<?php
    session_start();

    include_once 'restart.php';
    include_once 'undo.php';
    $db = include 'database.php';

    if (!isset($_SESSION['board']) || isset($_POST['restart'])) {
        initGame($db);
    } else if (isset($_POST['undo'])) {
        undoMove($_SESSION['last_move'], $db);
    }

    $lastMoveId = $_SESSION['last_move'];

    include_once 'util.php';

    $game_id = $_SESSION['game_id'];
    $board = $_SESSION['board'];
    $player = $_SESSION['player'];
    $hand = $_SESSION['hand'];

    echo $player;

    $to = [];

    //Alle posities rond een steen
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        //Alle posities op het bord waar een steen staat
        foreach (array_keys($board) as $pos) {
            //Key: ['0,0'] -> ['0','0'] De x en y positie worden opgeslagen in een array
            $pq2 = explode(',', $pos);
            // pq = ['0','1'], pq2 = ['0','0']
            // ($pq[0] + $pq2[0]) = 0 + 0
            //($pq[1] + $pq2[1]) = 1 + 0
            $position = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);

            if (count($board) >= 2) {
                if ($board[$pos][0][0] == $player) {
                    $to[] = $position;
                }
            } else {
                $to[] = $position;
            }
        }
    }
    // Removes duplicate keys
    // Causes issues with the key like the following:
    // Array(
    //    ...
    //    [8] => -1,1
    //    [11] => 2,-1
    // )
    $to = array_unique($to);
    if (!count($to)) $to[] = '0,0';
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

            Turn: <?php if ($player == 0) echo "White"; else echo "Black"; ?>
        </div>

        <!---------------- PLAY ---------------->
        <form method="post" action="play.php">
            <select name="piece">
                <?php
                    //$tile = [Q/B/S/A/G] - $remainingPieces = Amount left
                    foreach ($hand[$player] as $tile => $remainingPieces) {
                        if ($remainingPieces != 0) {
                            echo "<option value=\"$tile\">$tile</option>";
                        }
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    //$to = Array - $pos = 0,1 / 1,0 / etc.
                    foreach ($to as $pos) {
                        if(!array_key_exists($pos, $board)) {
                            if (count($board) >= 2) {
                                if (neighboursAreSameColor($player, $pos, $board)) {
                                    echo "<option value=\"$pos\">$pos</option>";
                                }
                            } else {
                                echo "<option value=\"$pos\">$pos</option>";
                            }
                        }
                    }
                ?>
            </select>
            <input type="submit" value="Play">
        </form>

        <!----------------  MOVE ---------------->
        <form method="post" action="move.php">
            <select name="from">
                <?php
                    foreach (array_keys($board) as $pos) {
                        if ($board[$pos][0][0] == $player) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($to as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" value="Move">
        </form>
        <form method="post" action="pass.php">
            <input type="submit" value="Pass">
        </form>
        <form method="post" action="index.php">
            <input type="submit" name="restart" value="Restart">
        </form>
        <strong><?php if (isset($_SESSION['error'])) echo($_SESSION['error']); unset($_SESSION['error']); ?></strong>
        <ol>
            <?php
                echo "Game id: " . $game_id . "<br>";
                echo "Last move id: " . $lastMoveId . (" Current move id: " . $lastMoveId + 1);
                $stmt = $db->prepare('SELECT * FROM moves WHERE game_id = '.$_SESSION['game_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_array()) {
                    echo '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
                }
            ?>
        </ol>
        <form method="post" action="index.php">
            <input type="submit" name="undo" value="Undo" <?php if ($lastMoveId <= '0'){ ?> disabled <?php   } ?>>
        </form>
    </body>
</html>

