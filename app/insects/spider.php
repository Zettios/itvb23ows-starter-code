<?php
    class spider implements insect {
        private hive_util $util;

        public function __construct(hive_util $util) {
            $this->util = $util;
        }

        public function calculate_move_position($from, $board): array {
            $movePositions = [];
            $fromPositionAsArray = explode(',', $from);
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                $surroundPositions = ($pq[0] + $fromPositionAsArray[0]) . ',' . ($pq[1] + $fromPositionAsArray[1]);
                if (isset($_SESSION['spider_moves'])) {
                    if (!array_key_exists($surroundPositions, $board)) {
                        $previousMoves = [];
                        foreach ($_SESSION['spider_moves'] as $spider_move) {
                            $previousMoves[] = $spider_move[0];
                        }
                        if (!in_array($surroundPositions, $previousMoves)) {
                            if ($this->util->can_tile_slide($board, $from, $surroundPositions)) {
                                $movePositions[] = $surroundPositions;
                            }
                        }
                    }
                } else {
                    if (!array_key_exists($surroundPositions, $board)) {
                        if ($this->util->can_tile_slide($board, $from, $surroundPositions)) {
                            $movePositions[] = $surroundPositions;
                        }
                    }
                }
            }
            return $movePositions;
        }
    }