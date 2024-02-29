<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';
require_once dirname(__DIR__) . '/../app/insects/insect.php';
require_once dirname(__DIR__) . '/../app/insects/beetle.php';

class BeetleTest extends TestCase {
    private hive_util $util;
    private beetle $beetle;

    protected function setUp(): void {
        $this->util = new hive_util();
        $this->beetle = new beetle($this->util);
    }

    public function test_get_beetle_move_positions() {
        $expected = [
            0 => "0,1",
            1 => "0,-1",
            2 => "1,0",
            3 => "1,-1"
        ];

        // Beetle is placed at 0,0
        $board = [
            "0,0" => [
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
        ];

        $this->assertEquals($expected, $this->beetle->calculate_move_position("0,0", $board));
    }

    public function test_get_beetle_move_positions_when_on_top_of_insect() {
        $expected = [
            0 => "1,1",
            1 => "1,-1",
            2 => "2,0",
            3 => "0,0",
            4 => "0,1",
            5 => "2,-1"
        ];

        // Beetle is placed on 1,0
        $board = [
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
        ];

        $this->assertEquals($expected, $this->beetle->calculate_move_position("1,0", $board));
    }

    public function test_move_insect_to_position_with_1_blocking_stack() {
        $expected = "1,-1";

        // Beetle is placed on 0,0
        $board = [
            "0,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ],
            ],
            "1,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ],
                1 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "0,-1" => [
                0 => [
                    0 => 1,
                    1 => "S"
                ]
            ],
        ];

        $this->assertContains($expected, $this->beetle->calculate_move_position("0,0", $board));
    }

    public function test_move_insect_to_position_with_2_blocking_stack() {
        $expected = "1,-1";

        // Beetle is placed on 0,0
        $board = [
            "0,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ],
            ],
            "1,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ],
                1 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "0,-1" => [
                0 => [
                    0 => 1,
                    1 => "S"
                ],
                1 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
        ];

        $this->assertNotContains($expected, $this->beetle->calculate_move_position("0,0", $board));
    }
}