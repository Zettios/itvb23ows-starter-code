<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';
require_once dirname(__DIR__) . '/../app/insects/insect.php';
require_once dirname(__DIR__) . '/../app/insects/grasshopper.php';

class GrasshopperTest extends TestCase {
    private grasshopper $grasshopper;

    protected function setUp(): void {
        $util = new hive_util();
        $this->grasshopper = new grasshopper($util);
    }

    public function test_get_grasshopper_move_values() {
        $expected = [
            0 => "0,3",
            1 => "-2,0",
            2 => "-2,2",
            3 => "2,-2"
        ];

        // Grasshopper is placed on 0,0
        $board = [
            "0,0" => [

            ],
            //Bottom right, jumping two tiles
            "0,1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "0,2" => [
                0 => [
                    0 => 1,
                    1 => "S"
                ]
            ],
            // Left, jumping one tile
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            // Bottom left, jumping one tile into surrounded spot
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "-2,1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "-3,2" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "-3,3" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "-2,3" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "-1,2" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            // Top right, jumping one tile
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ]
        ];
        $this->assertEqualsCanonicalizing($expected, $this->grasshopper->calculate_move_position("0,0", $board));
    }

    public function test_get_grasshopper_move_from_being_surrounded() {
        $expected = [
            0 => "-2,0",
            1 => "-2,2",
            2 => "0,-2",
            3 => "0,2",
            4 => "2,-2",
            5 => "2,0",
        ];

        // Grasshopper is placed on 0,0
        $board = [
            "0,0" => [

            ],
            //Bottom right
            "0,1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            //Top left
            "0,-1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            // Right
            "1,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            // Left
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            // Bottom left, jumping one tile into surrounded spot
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            // Top right, jumping one tile
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ]
        ];
        $this->assertEqualsCanonicalizing($expected, $this->grasshopper->calculate_move_position("0,0", $board));
    }
}