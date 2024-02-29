<?php
    interface insect {
        public function calculate_move_position($from, $board): array;

        public function move_insect($from, $to, $board);
    }