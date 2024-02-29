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

        $board = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
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
}