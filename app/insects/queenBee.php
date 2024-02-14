<?php
    class queenBee implements insect {
        private $db;

        public function __construct($db) {
            $this->db = $db;
        }

        public function move_insect($from, $to, $board) {
            echo "Moving queen";
        }
    }