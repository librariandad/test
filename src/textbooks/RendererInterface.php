<?php


namespace Medlib\Textbooks;


interface RendererInterface
{
    public function render(String $path, String $config):array;
}