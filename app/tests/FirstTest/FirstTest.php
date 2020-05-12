<?php
declare(strict_types=1);

namespace App\Tests\FirstTest;

use PHPUnit\Framework\TestCase;

class FirstTest extends TestCase
{
    public function testWillRunFirstAssert(): void
    {
        $this->assertEquals(5 * 2, 20 - 10);
        $this->assertIsInt((int)10.20);
        $this->assertEquals(10, (int)(5.05 * 2));
    }
}
