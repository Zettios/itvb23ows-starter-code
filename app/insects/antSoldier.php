<?php
    class antSoldier implements insect {
        private hive_util $util;

        public function __construct(hive_util $util) {
            $this->util = $util;
        }

        public function calculate_move_position($from, $board): array {
            $antMovementPositions = [];

            // Go through all positions on the keyboard to get the outer edges
            foreach (array_keys($board) as $boardPosition) {
                // Don't check from the ants location
                if ($boardPosition != $from) {
                    $boardPositionAsArray = explode(',', $boardPosition);
                    foreach ($GLOBALS['OFFSETS'] as $pq) {
                        // Check if the surrounding positions don't already exist
                        // Exist = not a valid spot to move to
                        // Don't exist = ant can MOST LIKELY move there
                        $surroundingPosition = ($pq[0] + $boardPositionAsArray[0]) . ',' . ($pq[1] + $boardPositionAsArray[1]);
                        if (!array_key_exists($surroundingPosition, $board)) {
                            if (!in_array($surroundingPosition, $antMovementPositions)) {
                                // Check if it can slide from that position
                                // else it's not a valid space the ant can move to
                                $surroundingPositionAsArray = explode(',', $surroundingPosition);
                                foreach ($GLOBALS['OFFSETS'] as $pq2) {
                                    $potentialPositionToSlideTo = ($pq2[0] + $surroundingPositionAsArray[0]) . ',' . ($pq2[1] + $surroundingPositionAsArray[1]);
                                    if (!array_key_exists($potentialPositionToSlideTo, $board)) {
                                        if ($this->util->can_tile_slide($board, $surroundingPosition, $potentialPositionToSlideTo)) {
                                            $antMovementPositions[] = $surroundingPosition;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $antMovementPositions;
        }

        public function move_insect($from, $to, $board) {
            echo "Moving ant soldier";
        }
    }