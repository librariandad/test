<?php


namespace Medlib\Textbooks;


interface ParserInterface
{
    public static function parse(String $path, String $config):array;
}