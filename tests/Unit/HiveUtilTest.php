<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';

final class HiveUtilTest extends TestCase {
    private hive_util $util;

    protected function setUp(): void {
        $this->util = new hive_util();
    }

//    public function test_positions_are_neighbours() {
//
//    }
//
    public function test_neighbours_are_the_same_color() {
        $expected = '';
        $player = 0;
        $position = "0,0";
        $board = [];

        $this->util->neighbours_are_same_color_new($player, $position, $board);

        self::assertEquals($expected, '');
    }

    public function test_len_is_one() {
        $this->util = new hive_util();
        $expected = 1;
        $testVar = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ]
        ];

        $result = $this->util->len($testVar);

        $this->assertEquals($expected, $result);
    }
}