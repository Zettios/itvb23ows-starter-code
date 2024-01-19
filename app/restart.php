<?php
    function initGame($db) {
        $_SESSION['board'] = [];
        $_SESSION['hand'] = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
            1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0;
        $_SESSION['last_move'] = 0;

        $db->prepare('INSERT INTO games VALUES ()')->execute();
        $_SESSION['game_id'] = $db->insert_id;
    }
?>