<?php

use PHPUnit\Framework\TestCase;

final class GameManagerTest extends TestCase
{
    public function testIsSame()
    {
        $string = 'user@example.com';

        $this->assertSame('user@example.com', $string);
    }
}