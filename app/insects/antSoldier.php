<?php
    class antSoldier implements insect {
        private hive_util $util;
        private array $antMovementPositions = [];

        public function __construct(hive_util $util) {
            $this->util = $util;
        }

        public function calculate_move_position($from, $board): array {
            foreach (array_keys($board) as $boardPosition) {
                if ($boardPosition != $from) {
                    $boardPositionAsArray = explode(',', $boardPosition);
                    foreach ($GLOBALS['OFFSETS'] as $pq) {
                        $surroundingPosition = ($pq[0] + $boardPositionAsArray[0]) . ',' . ($pq[1] + $boardPositionAsArray[1]);
                        $this->check_valid_positions($surroundingPosition, $board);
                    }
                }
            }
            return $this->antMovementPositions;
        }

        public function check_valid_positions($surroundingPosition, $board) {
            if (!array_key_exists($surroundingPosition, $board)) {
                if (!in_array($surroundingPosition, $this->antMovementPositions)) {
                    $surroundingPositionAsArray = explode(',', $surroundingPosition);
                    foreach ($GLOBALS['OFFSETS'] as $pq2) {
                        $potentialPositionToSlideTo = ($pq2[0] + $surroundingPositionAsArray[0]) . ',' . ($pq2[1] + $surroundingPositionAsArray[1]);
                        if (!array_key_exists($potentialPositionToSlideTo, $board)) {
                            if ($this->util->can_tile_slide($board, $surroundingPosition, $potentialPositionToSlideTo)) {
                                $this->antMovementPositions[] = $surroundingPosition;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }