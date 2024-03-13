<?php

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';
require_once dirname(__DIR__) . '/../app/game_manager/game_manager.php';
require_once dirname(__DIR__) . '/../app/database.php';

final class GameManagerTest extends TestCase {
    private game_manager $game_manager;

    private Stub $database_stub;
    private Stub $mysql_conn_stub;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void {
        $util = new hive_util();

        $this->database_stub = $this->createStub(database::class);
        $this->mysql_conn_stub = $this->createStub(mysqli::class);

        $this->game_manager = new game_manager($this->database_stub, $util);
    }

    public function test_show_all_playable_pieces_while_having_played_a_beetle_and_spider() {
        $expected = [
            0 => "Q",
            1 => "B",
            2 => "S",
            3 => "A",
            4 => "G"
        ];

        $hand = [0 => ["Q" => 1, "B" => 1, "S" => 1, "A" => 3, "G" => 3],
                1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $result = $this->game_manager->get_playable_tiles($hand, 0);

        $this->assertSame($expected, $result);
    }

    public function test_show_all_playable_pieces_except_all_played_ants() {
        $expected = [
            0 => "Q",
            1 => "B",
            2 => "S",
            3 => "G"
        ];

        $hand = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 0, "G" => 3],
                 1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $result = $this->game_manager->get_playable_tiles($hand, 0);

        $this->assertSame($expected, $result);
    }

    public function test_played_all_pieces() {
        $expected = [];

        $hand = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                1 => ["Q" => 0, "B" => 0, "S" => 0, "A" => 0, "G" => 0]];
        $result = $this->game_manager->get_playable_tiles($hand, 1);

        $this->assertSame($expected, $result);
    }

    public function test_player_black_wins() {
        $board = [
            // Queen to surround
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "0,1" => [
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
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "-1,1" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "S"
                ]
            ]
        ];
        $result = $this->game_manager->check_for_win($board);

        $this->assertFalse($result[0]);
        $this->assertTrue($result[1]);
    }

    public function test_player_white_wins() {
        $board = [
            // Queen to surround
            "0,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "0,1" => [
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
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "-1,1" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "S"
                ]
            ]
        ];
        $result = $this->game_manager->check_for_win($board);

        $this->assertTrue($result[0]);
        $this->assertFalse($result[1]);
    }

    public function test_player_white_queen_not_fully_surrounded() {
        $board = [
            // Queen to surround
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            "0,1" => [
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
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "-1,1" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "S"
                ]
            ]
        ];
        $_SESSION['last_made_moves'] = [
            0 => [
                0 => "0s000",
            ],
            1 => [
                0 => "1s111",
            ]
        ];

        $result = $this->game_manager->check_for_win($board);

        $this->assertFalse($result[0]);
        $this->assertFalse($result[1]);
    }

    public function test_draw() {
        // b w b w
        //b q b q w
        // w w b w
        $board = [
            // White queen to surround
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
            // Black queen to surround
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            // Top row
            "0,-1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "2,-1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "3,-1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            // Middle row
            "-1,0" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "3,0" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],

            // Bottom row
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "0,1" => [
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
            ],
            "2,1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],

        ];
        $result = $this->game_manager->check_for_win($board);

        $this->assertTrue($result[0]);
        $this->assertTrue($result[1]);
    }

    public function test_draw_by_stalemate() {
        $_SESSION['last_made_moves'] = [
            0 => [
                0 => "0s000",
                1 => "0s000",
                2 => "0s000",
                3 => "0s000",
                4 => "0s000",
                5 => "0s000",
            ],
            1 => [
                0 => "1s111",
                1 => "1s111",
                2 => "1s111",
                3 => "1s111",
                4 => "1s111",
                5 => "1s111",
            ]
        ];
        $board = [
            // White queen to surround
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0s000"
                ]
            ],
            // Black queen to surround
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "1s111"
                ]
            ]
        ];
        $result = $this->game_manager->check_for_win($board);

        $this->assertTrue($result[0]);
        $this->assertTrue($result[1]);
    }

    public function test_stalemate_check_where_player_still_uses_another_tile() {
        $_SESSION['last_made_moves'] = [
            0 => [
                0 => "0s000",
                1 => "0s000",
                2 => "0s001",
                3 => "0s000",
                4 => "0s000",
                5 => "0s000",
            ],
            1 => [
                0 => "1s111",
                1 => "1s111",
                2 => "1s111",
                3 => "1s111",
                4 => "1s111",
                5 => "1s111",
            ]
        ];

        $this->assertFalse($this->game_manager->check_for_stalemate());
    }

    public function test_player_must_pass_turn() {
        $playPositions = [];
        $movePositions = [];

        $this->assertTrue($this->game_manager->must_player_pass_turn($playPositions, $movePositions));
    }

    public function test_player_must_play() {
        $playPositions = [
            "0,0"
        ];
        $movePositions = [];

        $this->assertFalse($this->game_manager->must_player_pass_turn($playPositions, $movePositions));
    }

    public function test_player_must_move() {
        $playPositions = [
        ];
        $movePositions = [
            "0,0"
        ];

        $this->assertFalse($this->game_manager->must_player_pass_turn($playPositions, $movePositions));
    }

    public function test_undo_play() {
        $expected = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
        ];
        $expectedPlayer = 0;
        $_SESSION['last_move'] = -1;
        $_SESSION['hand'] = [0 => ["Q" => 0, "B" => 2, "S" => 3, "A" => 3, "G" => 3],
                             1 => ["Q" => 1, "B" => 2, "S" => 3, "A" => 3, "G" => 3]];
        $_SESSION['board'] = $expected;
        $_SESSION['player'] = 0;
        $_SESSION['spider_moves'] = [];
        $_SESSION['last_move'] = -1;
        $getPreviousMoveReturnResult = [
            0 => "280",
            1 => "73",
            2 => "play",
            3 => "Q",
            4 => "1,0",
            5 => "279",
            6 => $this->game_manager->get_game_state(),
        ];
        $this->database_stub ->method('get_previous_move')->willReturn($getPreviousMoveReturnResult);

        $this->game_manager->undo_move($_SESSION['last_move'], $this->mysql_conn_stub);
        $this->assertSame($expected, $_SESSION['board']);
        $this->assertEquals($expectedPlayer, $expectedPlayer);
    }

    public function test_undo_move() {
        $expected = [
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
        ];
        $expectedPlayer = 0;
        $_SESSION['last_move'] = -1;
        $_SESSION['hand'] = [];
        $_SESSION['board'] = $expected;
        $_SESSION['player'] = 0;
        $_SESSION['spider_moves'] = [];
        $getPreviousMoveReturnResult = [
            0 => "283",
            1 => "74",
            2 => "move",
            3 => "0,0",
            4 => "0,1",
            5 => "282",
            6 => $this->game_manager->get_game_state(),
        ];

        $this->database_stub ->method('get_previous_move')->willReturn($getPreviousMoveReturnResult);

        $this->game_manager->undo_move($_SESSION['last_move'], $this->mysql_conn_stub);
        $this->assertSame($expected, $_SESSION['board']);
        $this->assertEquals($expectedPlayer, $expectedPlayer);
    }

    public function test_calculate_correct_play_and_move_values() {
        $expectedPlayPositions = ["0,-1", "-1,0", "-1,1"];
        $expectedMovePositions = ["0,1", "1,-1"];
        $_SESSION['spider_moves'] = [];
        $player = 0;
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
            ]
        ];


        $playAndMovePositions = $this->game_manager->get_play_and_move_positions($board, $player);
        $this->assertSame($expectedPlayPositions, $playAndMovePositions[0]);
        $this->assertSame($expectedMovePositions, $playAndMovePositions[1]);
    }

    public function test_move_white_queen_to_lower_left_of_black() {
        $expected = [
                "1,0" => [
                    0 => [
                        0 => 1,
                        1 => "Q",
                        2 => "0"
                    ]
                ],
                "0,1" => [
                    0 => [
                        0 => 0,
                        1 => "Q",
                        2 => "0"
                    ]
                 ]
            ];

        $_POST['from'] = "0,0";
        $_POST['to'] = "0,1";
        $_SESSION['game_id'] = 0;
        $_SESSION['last_move'] = 0;
        $_SESSION['hand'] = [0 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                            1 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0; //white
        $_SESSION['board'] = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ]
        ];

        $this->game_manager->move_insect($this->mysql_conn_stub);
        $this->assertSame($expected, $_SESSION['board']);
    }

    public function test_place_tile_on_space_where_another_tile_moved_from() {
        $expected1 = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B",
                    2 => "0"
                ]
            ],
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "B",
                    2 => "0"
                ]
            ]
        ];
        $expected2 = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "B",
                    2 => "0"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B",
                    2 => "0"
                ]
            ]
        ];

        $_POST['from'] = "-1,0";
        $_POST['to'] = "-1,1";
        $_SESSION['game_id'] = 0;
        $_SESSION['last_move'] = 0;
        $_SESSION['hand'] = [0 => ["Q" => 0, "B" => 1, "S" => 2, "A" => 3, "G" => 3],
                            1 => ["Q" => 0, "B" => 1, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0; //white
        $_SESSION['board'] = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B",
                    2 => "0"
                ]
            ],
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "B",
                    2 => "0"
                ]
            ]
        ];

        $this->game_manager->move_insect($this->mysql_conn_stub);
        $this->assertSame($expected1, $_SESSION['board']);

        $_POST['from'] = "1,1";
        $_POST['to'] = "2,0";

        $this->game_manager->move_insect($this->mysql_conn_stub);
        $this->assertSame($expected2, $_SESSION['board']);

        $_POST['piece'] = "A";
        $_POST['to'] = "-1,0";

        $this->game_manager->play_insect($this->mysql_conn_stub);
        $this->assertArrayHasKey("-1,0", $_SESSION['board']);
        $this->assertEquals("0", $_SESSION['board']["-1,0"][0][0]);
        $this->assertEquals("A", $_SESSION['board']["-1,0"][0][1]);
    }

    public function test_player_white_must_play_queen() {
        $expected = 'Must play queen bee';

        $_POST['piece'] = "B";
        $_POST['to'] = "0,-2";

        $_SESSION['board'] = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "G"
                ]
            ],
            "0,1" => [
                0 => [
                    0 => 1,
                    1 => "S"
                ]
            ],
            "0,-1" => [
                0 => [
                    0 => 0,
                    1 => "S"
                ]
            ],
            "0,2" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "A"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ]
        ];
        $_SESSION['hand'] = [0 => ["Q" => 1, "B" => 2, "S" => 1, "A" => 2, "G" => 2],
                             1 => ["Q" => 1, "B" => 0, "S" => 1, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0; //white

        self::assertCount(6, $_SESSION['board']);
        self::assertEquals("B", $_POST['piece']);

        $this->game_manager->play_insect(null);
        self::assertEquals($expected, $_SESSION['error']);
    }

    public function test_player_black_must_play_queen() {
        $expected = 'Must play queen bee';

        $_POST['piece'] = "S";
        $_POST['to'] = "-1,3";

        $_SESSION['board'] = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "G"
                ]
            ],
            "0,1" => [
                0 => [
                    0 => 1,
                    1 => "S"
                ]
            ],
            "0,-1" => [
                0 => [
                    0 => 0,
                    1 => "S"
                ]
            ],
            "0,2" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "A"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "0,-2" => [
                0 => [
                    0 => 0,
                    1 => "Q"
                ]
            ],
        ];
        $_SESSION['hand'] = [0 => ["Q" => 0, "B" => 2, "S" => 1, "A" => 2, "G" => 2],
                            1 => ["Q" => 1, "B" => 0, "S" => 1, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 1; //black

        self::assertCount(7, $_SESSION['board']);
        self::assertEquals("S", $_POST['piece']);

        $this->game_manager->play_insect(null);
        self::assertEquals($expected, $_SESSION['error']);
    }

    public function test_move_spider_to_three_spaces() {
        $_POST['from'] = "-1,0";
        $_POST['to'] = "0,-1";
        $_SESSION['game_id'] = 0;
        $_SESSION['last_move'] = 0;
        $_SESSION['hand'] = [0 => ["Q" => 0, "B" => 2, "S" => 1, "A" => 3, "G" => 3],
            1 => ["Q" => 0, "B" => 1, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0; //white
        $_SESSION['spider_moves'] = [];
        $_SESSION['last_made_moves'] = [
            0 => [],
            1 => []
        ];
        $_SESSION['board'] = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "S",
                    2 => "0"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B",
                    2 => "0"
                ]
            ],
        ];

        $expectedBoard = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "0,-1" => [
                0 => [
                    0 => 0,
                    1 => "S",
                    2 => "0"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B",
                    2 => "0"
                ]
            ],
        ];
        $expectedSpiderMoves = [
            0 => [
                0 => "-1,0",
                1 => "0,-1"
            ]
        ];

        $this->game_manager->move_insect($this->mysql_conn_stub);
        $this->assertFalse(isset($_SESSION['error']));
        $this->assertEqualsCanonicalizing($expectedBoard, $_SESSION['board']);
        $this->assertEqualsCanonicalizing($expectedSpiderMoves, $_SESSION['spider_moves']);
        $this->assertEquals(0, $_SESSION['player']);
        $this->assertCount(1, $_SESSION['spider_moves']);

        $_POST['from'] = "0,-1";
        $_POST['to'] = "1,-1";
        $expectedBoard = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,-1" => [
                0 => [
                    0 => 0,
                    1 => "S",
                    2 => "0"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B",
                    2 => "0"
                ]
            ],
        ];
        $expectedSpiderMoves = [
            0 => [
                0 => "-1,0",
                1 => "0,-1"
            ],
            1 => [
                0 => "0,-1",
                1 => "1,-1"
            ]
        ];
        $this->game_manager->move_insect($this->mysql_conn_stub);
        $this->assertFalse(isset($_SESSION['error']));
        $this->assertEqualsCanonicalizing($expectedBoard, $_SESSION['board']);
        $this->assertEqualsCanonicalizing($expectedSpiderMoves, $_SESSION['spider_moves']);
        $this->assertEquals(0, $_SESSION['player']);
        $this->assertCount(2, $_SESSION['spider_moves']);


        $_POST['from'] = "1,-1";
        $_POST['to'] = "2,-1";
        $expectedBoard = [
            "0,0" => [
                0 => [
                    0 => 0,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q",
                    2 => "0"
                ]
            ],
            "2,-1" => [
                0 => [
                    0 => 0,
                    1 => "S",
                    2 => "0"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B",
                    2 => "0"
                ]
            ],
        ];
        $this->game_manager->move_insect($this->mysql_conn_stub);
        $this->assertFalse(isset($_SESSION['error']));
        $this->assertEqualsCanonicalizing($expectedBoard, $_SESSION['board']);
        $this->assertEqualsCanonicalizing([], $_SESSION['spider_moves']);
        $this->assertEquals(1, $_SESSION['player']);
        $this->assertCount(0, $_SESSION['spider_moves']);
    }
}