<?php


namespace Medlib\Textbooks;


interface ParserInterface
{
    public function render(String $path, String $config):array;
}