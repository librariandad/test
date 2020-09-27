<?php
/**
 * This file is part of the Medlib\Parser component.
 *
 * @fileName BootstrapInterface.php
 * @version 0.1
 * @author Keith Engwall <engwall@oakland.edu>
 * @copyright (c) Oakland University William Beaumont (OUWB) Medical Library
 * @license MIT
 */

namespace Medlib\Parser;

/**
 * Interface BootstrapInterface
 * @package Medlib\Parser
 */
interface BootstrapInterface
{
    /**
     * bootstrap() sets up error handling and reads the configuration file
     * and returns a config array.
     *
     * @param string $config_path
     * @return array
     */
    public static function bootstrap(string $config_path): array;
}