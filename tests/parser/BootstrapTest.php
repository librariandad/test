<?php

namespace Medlib\Parser;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

include "Setup.php";

class BootstrapTest extends TestCase
{
    public function setup():void
    {
        $this->root = vfsStream::setup('root');

        vfsStream::create(setupConfig());
    }

    public function testMissingConfig()
    {
        $this->expectExceptionMessage('file_get_contents(bleah): failed to open stream: No such file or directory');
        Bootstrap::bootstrap("bleah");
    }

    public function testInvalidConfig()
    {
        $this->expectExceptionMessage('File vfs://root/empty_config.json does not contain array with key paths');
        Bootstrap::bootstrap(vfsStream::url('root/empty_config.json'));
    }

    public function testValidConfig()
    {
        $this->assertArrayHasKey('paths', Bootstrap::bootstrap(vfsStream::url('root/config.json')));

    }
}
