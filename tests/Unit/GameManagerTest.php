<?php

use PHPUnit\Framework\MockObject\MockObject;
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

        //$this->database_stub->method("insert_player_move")->willReturn(1);
        $this->game_manager = new game_manager($this->database_stub, $util);
    }

    public function test_place_tile_on_space_where_another_tile_moved() {
        $expected1 = [
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
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ]
        ];

        $expected2 = [
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
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "2,0" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ]
        ];

        $expected3 = [
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
            "-1,1" => [
                0 => [
                    0 => 0,
                    1 => "B"
                ]
            ],
            "2,0" => [
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
                    1 => "Q"
                ]
            ],
            "1,0" => [
                0 => [
                    0 => 1,
                    1 => "Q"
                ]
            ],
            "1,1" => [
                0 => [
                    0 => 1,
                    1 => "B"
                ]
            ],
            "-1,0" => [
                0 => [
                    0 => 0,
                    1 => "B"
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
        $this->assertSame($expected3, $_SESSION['board']);
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
}