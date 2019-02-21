<?php namespace MoesifLaravel42\MoesifLaravel\Tests;

use MoesifLaravel42\MoesifLaravel\Tests\MoesifLaravelTestCase;

class MoesifLaravelTest extends MoesifLaravelTestCase
{
    /** @test */
    public function foo()
    {
        $res = $this->call('GET', 'test');
    }
}
