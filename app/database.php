<?php
class database {
    function connect_to_database(): mysqli {
        return new mysqli('db', 'root', 'password1234', 'hive');
    }

    function insert_player_move($db_conn, $type, $game_id, $from, $to, $last_move, $game_state) {
        $stmt = $db_conn->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) 
                                    values (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssis', $game_id, $type, $from, $to, $last_move, $game_state);
        $stmt->execute();
        $_SESSION['last_move'] = $db_conn->insert_id;

        return $db_conn->insert_id;
    }
}