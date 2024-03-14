<?php

class ai_handler {
    private static string $AI_API_URL = "http://hive-ai:5000";
    private static array $API_CONTENT_TYPE = array("Content-Type: application/json");

    function request_ai_response(){
        $testArray = [
            "move_number" => 8,
            "hand" => [
                ["Q" => 0, "B" => 2, "A" => 3, "S" => 3, "G" => 3,],
                ["Q" => 0, "B" => 2, "A" => 3, "S" => 3, "G" => 3,],
            ],
            "board" => [
                "0,0" => [[0, "Q"]],
                "0,-1" => [[1, "Q"]],
                "1,0" => [[0, "B"], [1, "B"]],
                "0,-2" => [[1, "B"]],
                "1,-1" => [[0, "A"]],
                "1,-3" => [[1, "S"], [0, "B"]],
            ],
        ];

        //$content = json_encode(jsonfy_game_state());
        $content = json_encode($testArray);

        $curl = curl_init(ai_handler::$AI_API_URL);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ai_handler::$API_CONTENT_TYPE);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response = json_decode($json_response, true);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }

    function jsonfy_game_state() {

    }
}