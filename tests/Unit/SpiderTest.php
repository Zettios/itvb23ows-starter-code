<?php

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';
require_once dirname(__DIR__) . '/../app/insects/insect.php';
require_once dirname(__DIR__) . '/../app/insects/spider.php';

class SpiderTest extends TestCase {
    private spider $spider;
    private game_manager $game_manager;

    private Stub $mysql_conn_stub;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void {
        $util = new hive_util();
        $this->spider = new spider($util);

        $database_stub = $this->createStub(database::class);
        $this->mysql_conn_stub = $this->createStub(mysqli::class);

        $this->game_manager = new game_manager($database_stub, $util);
    }

    public function test_get_spider_move_positions() {
        $expected = [
            0 => "0,1",
            1 => "1,-1"
        ];

        $board = [
            "0,0" => [
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ]
        ];
        $this->assertEqualsCanonicalizing($expected, $this->spider->calculate_move_position("0,0", $board));
    }

    public function test_get_spider_move_positions_when_moving() {
        $expected = [
            0 => "0,1"
        ];

        $_SESSION['spider_moves'] = [
            0 => [
                0 => "-1,0",
                1 => "-1,1"
            ]
        ];

        $board = [
            "0,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "-1,1" => [

            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
        ];
        $this->assertEqualsCanonicalizing($expected, $this->spider->calculate_move_position("-1,1", $board));
    }
}