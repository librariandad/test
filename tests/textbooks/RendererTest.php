<?php


use Medlib\Textbooks\Renderer;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    public function setup():void
    {
        $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
    }

    public function testConstants()
    {
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'].'/src/config.php',Renderer::CONFIG_PATH);
        $this->assertIsString(Renderer::RENDER_DEBUG);
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'].'/src/../log/renderer.log',Renderer::DEBUG_LOG_PATH);
        $this->assertSame(Monolog\Logger::DEBUG,Renderer::DEBUG_LOG_LEVEL);
        $this->assertIsInt(Renderer::DEBUG_NUM_LOGS);
        $this->assertIsString(Renderer::RENDER_ERROR);
        $this->assertIsArray(Renderer::ERROR_MAP);
    }

    public function testFaultyConfig()
    {
        $this->expectException(Error::class);
        Renderer::render("M1","/");
    }

    public function tearDown(): void
    {
        unset($_SERVER['DOCUMENT_ROOT']);
    }

}
