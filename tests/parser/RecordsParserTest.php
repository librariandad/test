<?php

namespace Medlib\Parser;

use PHPUnit\Framework\TestCase;

class RecordsParserTest extends TestCase
{
    private $root;

    public function setup():void
    {
        $this->root = getSetupPath();
    }

    public function testInvalidPage()
    {
        $this->expectExceptionMessage('bleah not in page map.');
        RecordsParser::parseRecords("bleah",$this->root.'data/config.json');
    }

    public function testInvalidConfig()
    {
        $this->expectExceptionMessage("File ".$this->root."data/empty_config.json does not contain array with key 'paths'");
        RecordsParser::parseRecords("DEBUG", $this->root.'data/empty_config.json');
    }

    public function testMissingConfig()
    {
        $this->expectExceptionMessage('file_get_contents('.realpath(dirname(__DIR__.'/../../src/parser/config.json')).'/config.json): failed to open stream: No such file or directory');
        RecordsParser::parseRecords("DEBUG");
    }

    public function testValidPageRequests()
    {
        $this->assertArrayHasKey('invalid', RecordsParser::parseRecords("DEBUG",$this->root.'data/config.json'));
        $records = RecordsParser::parseRecords("M1", $this->root.'data/config.json');
        $this->assertStringContainsString('Anatomical',$records[0]['groupName']);
    }
}
