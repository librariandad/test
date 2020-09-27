<?php

namespace Medlib\Parser;

use PHPUnit\Framework\TestCase;
use Monolog\Logger;

class RecordsParserTest extends TestCase
{
    public function setup():void
    {
        $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
    }

    public function testConstants()
    {
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'].'/src/config.php',RecordsParser::CONFIG_PATH);
        $this->assertIsString(RecordsParser::PARSE_DEBUG);
    }

    public function testFaultyConfig()
    {
        $this->expectException(\Exception::class);
        RecordsParser::parseRecords("M1","/");
    }

    public function tearDown(): void
    {
        unset($_SERVER['DOCUMENT_ROOT']);
    }

}
