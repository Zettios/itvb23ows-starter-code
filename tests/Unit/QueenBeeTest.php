<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';
require_once dirname(__DIR__) . '/../app/insects/insect.php';
require_once dirname(__DIR__) . '/../app/insects/queenBee.php';

class QueenBeeTest extends TestCase {
    private hive_util $util;
    private queenBee $queenBee;

    protected function setUp(): void {
        $this->util = new hive_util();
        $this->queenBee = new queenBee($this->util);
    }

    public function test_no_non_slidable_positions() {
        $expected = "0,1";

        $board = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "1,0" => [

            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
        ];

        $this->assertNotContains($expected, $this->queenBee->calculate_move_position("1,0", $board));
    }
}