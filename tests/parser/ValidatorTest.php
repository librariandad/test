<?php

namespace Medlib\Parser;

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{

    private $root;

    public function setup():void
    {
        $this->root = getSetupPath();
    }
    public function testValidateData()
    {
        $this->assertTrue(Validator::validateData("http://github.com",array("method" => "url")));
        $this->assertTrue(Validator::validateData("1984", array("method" => "date","args" => ["Y"])));
    }

    public function testBadMethodCall()
    {
        $this->expectExceptionMessage("Undefined index: method");
        Validator::validateData("1984", array("date","Y"));
    }

    public function testBadArgsCall()
    {
        $this->expectExceptionMessage("Invalid argument supplied for foreach()");
        Validator::validateData("1984", array("method" => "date","args" => "Y"));
    }
}
