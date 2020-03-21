<?php

namespace Kinodash\Tests\Unit\Dashboard;

use Kinodash\Dashboard\Spot;
use Kinodash\Tests\TestCase;

class SpotTest extends TestCase
{
    public function test_it_can_be_compared_for_equality(): void
    {
        $spot = Spot::HEAD();

        $this->assertEquals($spot, Spot::HEAD());
        $this->assertNotEquals($spot, Spot::SCRIPT());
        $this->assertTrue($spot->equals(Spot::HEAD()));
        $this->assertFalse($spot->equals(Spot::MIDDLE_CENTER()));
    }

    public function test_it_can_be_used_as_array_key(): void
    {
        $spotList = [(string)Spot::MIDDLE_CENTER() => 'test'];

        $this->assertArrayHasKey((string)Spot::MIDDLE_CENTER(), $spotList);
    }
}
