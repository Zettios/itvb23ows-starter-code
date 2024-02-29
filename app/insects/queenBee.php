<?php
    class queenBee implements insect {
        private hive_util $util;

        public function __construct(hive_util $util) {
            $this->util = $util;
        }

        public function calculate_move_position($from, $board): array {
            $movePositions = [];
            $fromPositionAsArray = explode(',', $from);
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $surroundPositions = ($pq[0] + $fromPositionAsArray[0]) . ',' . ($pq[1] + $fromPositionAsArray[1]);
                if (!array_key_exists($surroundPositions, $board)) {
                    if ($this->util->can_tile_slide($board, $from, $surroundPositions)) {
                        $movePositions[] = $surroundPositions;
                    }
                }
            }
            return $movePositions;
        }

        public function move_insect($from, $to, $board) {
            echo "Moving queen";
        }
    }