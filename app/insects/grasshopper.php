<?php
    class grasshopper implements insect {
        private hive_util $util;

        public function __construct($util) {
            $this->util = $util;
        }

        public function calculate_move_position($from, $board): array {
            $movePositions = [];
            $fromPositionAsArray = explode(',', $from);
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $surroundPositions = ($pq[0] + $fromPositionAsArray[0]) . ',' . ($pq[1] + $fromPositionAsArray[1]);
                if (array_key_exists($surroundPositions, $board)) {
                    $foundOpenSpace = false;
                    while (!$foundOpenSpace) {
                        $surroundPositionAsArray = explode(',', $surroundPositions);
                        $surroundPositions = ($pq[0] + $surroundPositionAsArray[0]) . ',' . ($pq[1] + $surroundPositionAsArray[1]);

                        if (!array_key_exists($surroundPositions, $board)) {
                            $movePositions[] = $surroundPositions;
                            echo "Surround position is: ".$surroundPositions;
                            $foundOpenSpace = true;
                        }
                    }
                }
            }
            return $movePositions;
        }

        public function move_insect($from, $to, $board) {
            echo "Moving grasshopper";
        }
    }