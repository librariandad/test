<?php


namespace Medlib\Parser;


interface RecordsParserInterface
{
    public static function parseRecords(String $path, String $config):array;
}