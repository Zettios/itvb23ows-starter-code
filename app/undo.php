<?php
    function undoMove($lastMove, $db) {
        echo $lastMove;

        $stmt = $db->prepare('SELECT * FROM moves WHERE id = '.$lastMove);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();

        echo '<pre>';
        echo print_r($result);
        echo '</pre>';

        $_SESSION['last_move'] = $result[5];
        set_state($result, $result[6], $result[2], $result[4]);

        $stmt = $db->prepare('DELETE FROM moves WHERE id = '.$lastMove);
        $stmt->execute();
    }
