<?php
    class spider implements insect {
        public function __construct() {
        }

        public function calculate_move_position($from, $board): array {
            return [];
        }

        public function move_insect($from, $to, $board) {
            echo "Moving spider";
        }
    }