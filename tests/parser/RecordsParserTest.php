<?php

namespace Medlib\Parser;

use PHPUnit\Framework\TestCase;

use Medlib\Parser\RecordsParser;

class RecordsParserTest extends TestCase
{

    public function testInvalidPageRequest()  {
        $this->expectException(\Exception::class);
        RecordsParser::parseRecords("bleah");
    }

    public function testDebugRequest() {
        $this->assertIsArray(RecordsParser::parseRecords("DEBUG", "src/parser/config.json.example"));
    }
}
