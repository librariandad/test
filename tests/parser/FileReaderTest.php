<?php

namespace OUWBMedlib\Parser;

use PHPUnit\Framework\TestCase;

class FileReaderTest extends TestCase
{
    private $root;

    public function setup():void
    {
        $this->root = getSetupPath();
    }
    public function testReadCSV()
    {
        $csv = FileReader::readCSV($this->root."data/records.csv", "Course","Author");
        $this->assertArrayHasKey('Title', $csv[1]);
    }

    public function testCSVFileNotFoundException()
    {
        $this->expectExceptionMessage("`".$this->root."data/record.csv`: failed to open stream: No such file or directory");
        FileReader::readCSV($this->root."data/record.csv", "Courses","Author");
    }

    public function testGroupByException()
    {
        $this->expectExceptionMessage("Record data file does not have expected header 'Courses'");
        FileReader::readCSV($this->root."data/records.csv", "Courses","Author");
    }

    public function testSortFieldException()
    {
        $this->expectExceptionMessage("Record data file does not have expected header 'Authors'");
        FileReader::readCSV($this->root."data/records.csv", "Course","Authors");
    }

    public function testReadJSON()
    {
        $json = FileReader::readJSON($this->root."data/page_map.json","pages");
        $this->assertArrayHasKey("M1", $json["pages"]);
    }

    public function testReadJSONException()
    {
        $this->expectExceptionMessage("File ".$this->root."data/page_map.json does not contain array with key 'page'");
        FileReader::readJSON($this->root."data/page_map.json","page");
    }

    public function testJSONFileNotFoundException()
    {
        $this->expectExceptionMessage("file_get_contents(".$this->root."data/page_maps.json): failed to open stream: No such file or directory");
        FileReader::readJSON($this->root."data/page_maps.json","page");
    }

}
