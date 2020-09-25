<?php
/**
 * This file is part of the Medlib\Textbooks component.
 *
 * @filename config.php contains paths for files required for the
 * display of textbook data.
 *
 * @version 0.1
 * @configFor Renderer.php
 * @author Keith Engwall
 * @copyright (c) Oakland University William Beaumont School of Medicine Library
 * @license MIT
 */

/**
 * Use include() to pass the contents of this file into a variable:
 * $config = include('config.php');
 */
return [

    /**
     * Paths to required files:
     * - textbook_data (csv) contains textbook data
     * - page_map (php) contains the map used to define textbook sets on each page
     * - template (php) contains the display template for textbook data
     */
    'paths' => [
        'textbook_data' => __DIR__.'/../../data/textbooks.csv',
        'page_map' => __DIR__.'/../../data/page_map.php',
        'log' => __DIR__.'/../../log/renderer.log'
    ],

    /**
     * Specify the field in the csv that contains the keys to be used
     * for each set of textbooks (e.g. course ids) as well as (optionally)
     * the delimiter used to separate the keys (e.g. '|' for 'KEY1|KEY2|KEY3')
     * If no delimiter is specified, it will be assumed that the
     * keys are comma delimited within double quotes (e.g. '"KEY1, KEY2, KEY3"')
     */
    'group_by' => [
        'field' => 'Course',
        'delim' => '|'
    ],

    /**
     * Specify the exact header used for the field by which you wish
     * textbook data to be sorted
     */
    'book_sort' => 'Author',

    /**
     * Rules used to validate textbook data.
     *
     * To specify a validation rule for a field, use the format
     *
     * 'fieldname' => ['method']
     *
     * 'fieldname' should exactly match the header in textbook_data
     *
     * 'method' should be one of the following available validation methods:
     * - 'string'     - checks whether or not the field value is a string
     * - 'isbn'       - checks that the isbn is in a valid format
     * - 'url'        - checks for a valid url
     * - 'year'       - checks for a 4-digit year
     * - 'edition'    - checks the ordinal format of the edition
     */
    'validation' => [
        'Title' => ['string'],
        'Author' => ['string'],
        'ISBN' => ['isbn'],
        'URL' => ['url'],
        'Year' => ['year'],
        'Edition' => ['edition']
    ]
];
?>
