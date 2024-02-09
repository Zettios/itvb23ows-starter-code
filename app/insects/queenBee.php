<?php
    class queenBee implements insect {
        private $db;

        public function __construct($db) {
            $this->db = $db;
        }

        public function move_insect($from, $to, $board) {
            if (!can_slide($from, $to, $board)) {
                $_SESSION['error'] = "Tile can't slide to this position";
            } else if(!has_neighbour_new($from, $to, $board)) {
                $_SESSION['error'] = "Tile must have a neighbour";
            } else {
                $this->move_queen($board, $from, $to);
            }
        }

        public function move_queen($board, $from, $to){
            $tile = array_pop($board[$from]);
            $board[$to] = [$tile];

            if (empty($board[$from])) {
                unset($board[$from]);
            }

            //insert_move_in_db($from, $to, $board, $this->db);
        }
    }