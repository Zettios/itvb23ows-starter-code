<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';
require_once dirname(__DIR__) . '/../app/insects/insect.php';
require_once dirname(__DIR__) . '/../app/insects/grasshopper.php';

class GrasshopperTest extends TestCase {
    private hive_util $util;

    protected function setUp(): void {
        $this->util = new hive_util();
    }

    public function test_assert_true() {
        $this->assertTrue(true);
    }
}