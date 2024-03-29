<?php
class database {
    function connect_to_database(): mysqli {
        return new mysqli('db', 'root', '7cfNu1k77xeI2MQ6YFQ8g6rMsZ9NI2I', 'hive');
    }

    function insert_player_move($db_connection, $type, $game_id, $from, $to, $last_move, $game_state) {
        $stmt = $db_connection->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) 
                                    values (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssis', $game_id, $type, $from, $to, $last_move, $game_state);
        $stmt->execute();
        $_SESSION['last_move'] = $db_connection->insert_id;

        return $db_connection->insert_id;
    }

    function insert_player_pass($db_connection) {
        $stmt = $db_connection->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $_SESSION['game_id'], $_SESSION['last_move'], $this->get_game_state());
        $stmt->execute();
    }

    function get_previous_move($db_connection, $lastMove): array {
        $stmt = $db_connection->prepare('SELECT * FROM moves WHERE id = '.$lastMove);
        $stmt->execute();
        return $stmt->get_result()->fetch_array();
    }

    function delete_previous_move($db_connection, $lastMove) {
        $stmt = $db_connection->prepare('DELETE FROM moves WHERE id = '.$lastMove);
        $stmt->execute();
    }

    function get_game_history($db_connection, $game_id): mysqli_result {
        $stmt = $db_connection->prepare('SELECT * FROM moves WHERE game_id = '.$game_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}