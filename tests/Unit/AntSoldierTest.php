<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';
require_once dirname(__DIR__) . '/../app/insects/insect.php';
require_once dirname(__DIR__) . '/../app/insects/antSoldier.php';

final class AntSoldierTest extends TestCase {
    private hive_util $util;
    private antSoldier $antSoldier;

    protected function setUp(): void {
        $this->util = new hive_util();
        $this->antSoldier = new antSoldier($this->util);
    }

    public function test_get_ant_soldier_positions() {
        $expected = [
            0 => "1,1",
            1 => "1,-1",
            2 => "2,0",
            3 => "0,1",
            4 => "2,-1"
        ];

        $board = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "A"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
        ];


        $this->assertEqualsCanonicalizing($expected, $this->antSoldier->calculate_move_position("0,0", $board));
    }

    public function test_get_ant_soldier_positions_with_surrounded_tile() {
        $expected = [
            0 => "0,1",
            1 => "-1,1",
            2 => "1,1",
            3 => "2,0",
            4 => "3,-1",
            5 => "3,-2",
            6 => "0,-2",
            7 => "-1,-1",
            8 => "1,-2",
            9 => "2,-3",
            10 => "3,-3"
        ];

        $board = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "A"
                ]
            ],
            "2,-1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "0,-1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "2,-2" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
        ];


        $this->assertEqualsCanonicalizing($expected, $this->antSoldier->calculate_move_position("-1,0", $board));
    }
}