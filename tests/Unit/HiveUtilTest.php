<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';

final class HiveUtilTest extends TestCase {
    private hive_util $util;

    protected function setUp(): void {
        $this->util = new hive_util();
    }

    // BOTTOM RIGHT
    public function test_if_positions_are_bottom_right_neighbours() {
        $this->assertTrue($this->util->is_neighbour("0,1", "0,2"));
        $this->assertTrue($this->util->is_neighbour("4,10", "4,11"));
        $this->assertTrue($this->util->is_neighbour("-6,10", "-6,11"));
    }

    public function test_if_positions_are_not_bottom_right_neighbours() {
        $this->assertFalse($this->util->is_neighbour("0,1", "0,3"));
        $this->assertFalse($this->util->is_neighbour("2,4", "2,10"));
        $this->assertFalse($this->util->is_neighbour("-8,4", "-8,6"));
    }

    // TOP LEFT
    public function test_if_positions_are_top_left_neighbours() {
        $this->assertTrue($this->util->is_neighbour("0,-1", "0,-2"));
        $this->assertTrue($this->util->is_neighbour("10,-1", "10,-2"));
        $this->assertTrue($this->util->is_neighbour("-1,-4", "-1,-5"));
    }

    public function test_if_positions_are_not_top_left_neighbours() {
        $this->assertFalse($this->util->is_neighbour("0,-1", "0,-6"));
        $this->assertFalse($this->util->is_neighbour("10,-1", "10,-3"));
        $this->assertFalse($this->util->is_neighbour("-1,0", "-1,-5"));
    }

    // RIGHT
    public function test_if_positions_are_right_neighbours() {
        $this->assertTrue($this->util->is_neighbour("0,0", "1,0"));
        $this->assertTrue($this->util->is_neighbour("6,5", "6,6"));
        $this->assertTrue($this->util->is_neighbour("-10,2", "-9,2"));
    }

    public function test_if_positions_are_not_right_neighbours() {
        $this->assertFalse($this->util->is_neighbour("0,0", "4,0"));
        $this->assertFalse($this->util->is_neighbour("0,-3", "2,-3"));
        $this->assertFalse($this->util->is_neighbour("0,-10", "10,-10"));
    }

    // LEFT
    public function test_if_positions_are_left_neighbours() {
        $this->assertTrue($this->util->is_neighbour("0,0", "-1,0"));
        $this->assertTrue($this->util->is_neighbour("5,0", "4,0"));
        $this->assertTrue($this->util->is_neighbour("-4,0", "-5,0"));
    }

    public function test_if_positions_are_not_left_neighbours() {
        $this->assertFalse($this->util->is_neighbour("0,0", "-2,0"));
        $this->assertFalse($this->util->is_neighbour("5,5", "3,5"));
        $this->assertFalse($this->util->is_neighbour("-4,0", "-6,0"));
    }

    // BOTTOM LEFT
    public function test_if_positions_are_bottom_left_neighbours() {
        $this->assertTrue($this->util->is_neighbour("0,0", "-1,1"));
        $this->assertTrue($this->util->is_neighbour("-1,1", "-2,2"));
        $this->assertTrue($this->util->is_neighbour("-10,3", "-11,4"));
    }

    public function test_if_positions_are_not_bottom_left_neighbours() {
        $this->assertFalse($this->util->is_neighbour("0,0", "-2,2"));
        $this->assertFalse($this->util->is_neighbour("-2,2", "-4,4"));
        $this->assertFalse($this->util->is_neighbour("-12,6", "-15,8"));
    }

    // TOP RIGHT
    public function test_if_positions_are_top_right_neighbours() {
        $this->assertTrue($this->util->is_neighbour("0,0", "1,-1"));
        $this->assertTrue($this->util->is_neighbour("1,-1", "2,-2"));
        $this->assertTrue($this->util->is_neighbour("8,9", "9,8"));
    }

    public function test_if_positions_are_not_top_right_neighbours() {
        $this->assertFalse($this->util->is_neighbour("0,0", "2,-2"));
        $this->assertFalse($this->util->is_neighbour("2,-2", "6,-6"));
        $this->assertFalse($this->util->is_neighbour("6,6", "8,-4"));
    }

    public function test_neighbours_are_the_same_color() {
        $expected = '';
        $player = 0;
        $position = "0,0";
        $board = [];

        $this->util->neighbours_are_same_color_new($player, $position, $board);

        $this->assertEquals($expected, '');
    }

    public function test_len_is_one() {
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

    public function test_tile_can_slide_to_space() {
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
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ]
        ];
        $this->assertTrue($this->util->can_tile_slide($board, "0,0", "0,-1"));
    }

    public function test_tile_cannot_slide_to_space() {
        // o = open space | x = tile | m = tile moving | d = destination
        // o x d o
        //  o m x
        $board = [
            "0,0" => [

            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "0,-1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ]
        ];
        $this->assertFalse($this->util->can_tile_slide($board, "0,0", "1,-1"));
        // o = open space | x = tile | m = tile moving | d = destination
        // o x d
        //  o m x
        // o o o x
        $board = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "1,0" => [

            ],
            "0,-1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ]
        ];
        $this->assertFalse($this->util->can_tile_slide($board, "1,0", "-1,1"));
    }
}