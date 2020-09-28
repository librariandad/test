<?php

namespace Medlib\Parser;

use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    private $root;

    public function setup():void
    {
        $this->root = getSetupPath();
    }
    public function testMissingConfig()
    {
        $this->expectExceptionMessage('file_get_contents(bleah): failed to open stream: No such file or directory');
        Bootstrap::bootstrap("bleah");
    }

    public function testInvalidConfig()
    {
        $this->expectExceptionMessage("File ".$this->root."data/empty_config.json does not contain array with key 'paths'");
        Bootstrap::bootstrap($this->root.'data/empty_config.json');
    }

    public function testValidConfig()
    {
        $this->assertArrayHasKey('paths', Bootstrap::bootstrap($this->root.'data/config.json'));

    }
}
