<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/../app/game_manager/hive_util.php';

final class HiveUtilTest extends TestCase {
    public function test_len_is_one() {
        $util = new hive_util();
        $expected = 1;
        $testVar = array(
            "0,0" => array(
                0,
                "Q"
            )
        );

        $result = $util->len($testVar);

        $this->assertEquals($expected, $result);
    }
}