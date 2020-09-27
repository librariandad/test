<?php
/**
 * This file is part of the Medlib\Parser component.
 *
 * @fileName ValidatorInterface.php
 * @version 0.1
 * @author Keith Engwall <engwall@oakland.edu>
 * @copyright (c) Oakland University William Beaumont (OUWB) Medical Library
 * @license MIT
 */


namespace Medlib\Parser;

/**
 * Interface ValidatorInterface
 * @package Medlib\Parser
 */
interface ValidatorInterface
{

    /**
     * validateData() validates record data based on a set of available rules
     *
     * @param string $data is the data value to be validated
     * @param array $rule is the rule used to validate the data
     * @return bool
     */
    public static function validateData(string $data, array $rule): bool;
}