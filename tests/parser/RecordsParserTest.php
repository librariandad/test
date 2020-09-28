<?php

namespace Medlib\Parser;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

// include "Setup.php";

class RecordsParserTest extends TestCase
{

    private $root;

    public function setup():void
    {
        $this->root = vfsStream::setup('root');

        vfsStream::create(setupConfig());
    }

    public function testInvalidPage()
    {
        $this->expectExceptionMessage('bleah not in page map.');
        RecordsParser::parseRecords("bleah", vfsStream::url('root/config.json'));
    }

    public function testInvalidConfig()
    {
        $this->expectExceptionMessage('File vfs://root/empty_config.json does not contain array with key paths');
        RecordsParser::parseRecords("DEBUG", vfsStream::url('root/empty_config.json'));
    }

    public function testMissingConfig()
    {
        $this->expectExceptionMessage('file_get_contents(/Library/WebServer/Documents/components/test/src/parser/config.json): failed to open stream: No such file or directory');
        RecordsParser::parseRecords("DEBUG");
    }

    public function testValidPageRequests()
    {
        $this->assertArrayHasKey('invalid', RecordsParser::parseRecords("DEBUG", vfsStream::url('root/config.json')));
        $records = RecordsParser::parseRecords("M1", vfsStream::url('root/config.json'));
        $this->assertStringContainsString('Anatomical',$records[0]['groupName']);
    }
}
