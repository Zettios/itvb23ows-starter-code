<?php
//    function undoMove($lastMove, $db) {
//        $stmt = $db->prepare('SELECT * FROM moves WHERE id = '.$lastMove);
//        $stmt->execute();
//        $result = $stmt->get_result()->fetch_array();
//
////        echo '<pre>';
////        echo print_r($result);
////        echo '</pre>';
//
//        $_SESSION['last_move'] = $result[5];
//        set_state($result);
//
//        $stmt = $db->prepare('DELETE FROM moves WHERE id = '.$lastMove);
//        $stmt->execute();
//    }
