<?php

class ai_handler {
    private database $database;
    private hive_util $util;

    private static string $AI_API_URL = "http://hive-ai:5000";
    private static array $API_CONTENT_TYPE = array("Content-Type: application/json");

    function __construct($database, $util) {
        $this->database = $database;
        $this->util = $util;
    }

    function request_ai_response(): array{
        $content = $this->jsonfy_game_state();
        $curl = curl_init(ai_handler::$AI_API_URL);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ai_handler::$API_CONTENT_TYPE);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        curl_close($curl);

        return json_decode($json_response, true);
    }

    function jsonfy_game_state(): string {
        $api_okay_board =  $_SESSION['board'];
        foreach (array_keys($api_okay_board) as $boardKey) {
            foreach (array_keys($api_okay_board[$boardKey]) as $tileKey) {
                unset($api_okay_board[$boardKey][$tileKey][2]);
            }
        }

        $json_game_state = array(
            'move_number' => $_SESSION['move_number'],
            'hand' => $_SESSION['hand'],
            'board' => $api_okay_board
        );

        $content = json_encode($json_game_state);
        return str_replace("\\", '', $content);
    }

    function process_ai_action($ai_action, $db_connection) {
        $type_action = $ai_action[0];
        switch ($type_action) {
            case "play":
                $this->process_ai_play($ai_action[1], $ai_action[2], $db_connection);
                $this->echo_ai_play($ai_action);
                break;
            case "move":
                $this->process_ai_move($ai_action[1], $ai_action[2], $db_connection);
                $this->echo_ai_move($ai_action);
                break;
            case "pass":
                $this->process_ai_pass($db_connection);
                break;
        }
    }

    function process_ai_play($piece, $to, $db_connection) {
        $player = $_SESSION['player'];
        $_SESSION['move_number']++;
        $_SESSION['board'][$to] = [[$player, $piece, $this->util->generate_tile_id($player, $piece)]];
        $_SESSION['hand'][$player][$piece]--;

        $state = $this->util->get_game_state();

        $_SESSION['player'] = 1 - $_SESSION['player'];

        $_SESSION['last_move'] = $this->database->insert_player_move(
            $db_connection,
            "play",
            $_SESSION['game_id'],
            $piece, $to,
            $_SESSION['last_move'],
            $state);
    }

    function process_ai_move($from, $to, $db_connection) {
        $_SESSION['move_number']++;
    }

    function process_ai_pass($db_connection) {
        $_SESSION['move_number']++;
    }

    function echo_ai_play($ai_action) {
        echo "<pre>";
        echo "The AI played ".$ai_action[1]." on position: ".$ai_action[2];
        echo "</pre>";
    }

    function echo_ai_move($ai_action) {
        echo "<pre>";
        echo "The AI moved ".$ai_action[1]." to position: ".$ai_action[2];
        echo "</pre>";
    }
}