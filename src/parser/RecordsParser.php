<?php
/**
 * This file is part of the Medlib\Textbooks component.
 *
 * @fileName RecordsParser.php
 * @version 0.1
 * @author Keith Engwall <engwall@oakland.edu>
 * @copyright (c) Oakland University William Beaumont (OUWB) Medical Library
 * @license MIT
 *
 * This class contains the static method parseCSV(), which will parse a
 * CSV file of records for display on a set of one or more related webpages.
 *
 * Records are grouped into lists according to a set of identifiers listed in
 * in a specified field within the CSV, and the lists are distributed among
 * webpages according to a json page map file.
 *
 * Record data may be validated by a specified set of validation rules.
 *
 * A json configuration file provides the paths to both the record file and
 * the page map file, as well as grouping and sorting fields, as well as validation
 * rules.
 *
 * This is the textbook rendering engine for the OUWB Medical Library website.
 * When the render method is passed the identifying string for a specific
 * curriculum year,it will read data from the library's textbook spreadsheet
 * and, using the configured page map, organize it for display as lists of
 * recommended and required parser for each course in that year.
 */

declare(strict_types=1);

namespace Medlib\Parser;

use League\Csv;
use Respect\Validation\Validator as validator;
use Whoops\Exception\Inspector;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
 * @className RecordsParser
 * @package Medlib\Textbooks
 * @implements RendererInterface
 */
class RecordsParser implements RecordsParserInterface
{
    const CONFIG_PATH = __DIR__.'/config.json'; // configuration file
    const PARSE_DEBUG = 'DEBUG'; // argument for debug mode

    /**
     * The parseRecords() function is called with a page ID string, which is used to
     * identify the set of grouping lists for the corresponding page as indicated
     * in the page map file.
     *
     * An alternative path to the configuration file may also be provided.
     *
     * @param string $pageIdRaw is the page requested by the user
     * @param string $configPathRaw is the path of the config file
     * @return array textbook data organized and sorted for display
     * @throws \Exception in DEBUG mode as a means of testing the page
     *                    after an update
     */
    public static function parseRecords(string $pageIdRaw, string $configPathRaw=self::CONFIG_PATH): array
    {
        // sanitize inputs
        $page_id = filter_var($pageIdRaw, FILTER_SANITIZE_STRING);
        $config_path = filter_var($configPathRaw, FILTER_SANITIZE_STRING);

        // set up error handling, read configurations, etc.
        $config = self::bootstrap($config_path);

        // get paths for textbook and course data
        $basePath = $config['paths']['base_path'];
        $mapPath = $basePath.$config['paths']['page_map'];
        $data_path = $basePath.$config['paths']['textbook_data'];

        // get course data
        $page_map = self::getPageMap($mapPath);

        // get textbook data
        $textbookData = self::getTextbookData($data_path, $config);

        // parse the textbook data according for requested page
        if ( $page_id == self::PARSE_DEBUG ) {
            // render page as DEBUG
            $result = self::parseData($page_id, $page_map, $textbookData);
        } elseif ( array_key_exists($page_id, $page_map) ) {
            // render requested page
            $result = self::parseData($page_id, $page_map[$page_id], $textbookData);
        } else {
            throw new \Exception($page_id." not in page map.");
        }

        return $result;
    }

    private static function bootstrap(string $config_path): array
    {
        // initialize error handling and format error page
        self::setupErrorHandling();

        // read configurations for file paths, sorting, validation, etc.
        $config = self::readValidatedFile($config_path, 'paths');


        return $config;
    }

    private static function getPageMap(string $path): array
    {
        // load page map data for courses
        $page_data = self::readValidatedFile($path, 'pages');
        return $page_data['pages'];
    }

    /**
     * getTextbookData()
     * @param string $path
     * @param array $config
     * @return array
     * @throws Csv\Exception
     */
    private static function getTextbookData(string $path, array $config): array
    {
        $textbookData = array();
        // get the textbook data records
        $textbookData['records'] = self::readCSV($path);
        // get the validation methods for the data
        $textbookData['validation'] = $config['validation'];
        // get the field data will be grouped by from the config file
        $textbookData['group_by'] = $config['group_by'];
        // get the field data will be sorted by from the config file
        $textbookData['sort'] = $config['book_sort'];

        return $textbookData;
    }

    /**
     * setupErrorHandling() configures the error handler
     */
    private static function setupErrorHandling() {

        // initialize error handler
        $whoops = new Run();
        $handler = new PrettyPageHandler();

        // add details table to error page
        $handler->addDataTableCallback(
            'Details',
            function(Inspector $inspector) {
                $data = array();
                $data['Message'] = $inspector->getExceptionMessage();
                $exception = $inspector->getException();
                $data['Exception class'] = get_class($exception);
                $data['Exception code'] = $exception->getCode();
                $data['Line'] = $exception->getLine();
                return $data;
            }
        );
        $whoops->pushHandler($handler);
        $whoops->register();
    }


    /**
     * readCSV() reads a csv file into an array keyed by column headers
     * @param string $path is the CSV file
     * @return array is the data array
     * @throws Csv\Exception
     */

    private static function readCSV(string $path):array
    {
        // build result array
        $result = array();

        // open the CSV in read mode
        $reader = Csv\Reader::createFromPath($path, 'r');

        // get the column headers
        $reader->setHeaderOffset(0);
        // $reader->getHeaderOffset(); //returns 0
        $data = $reader->getRecords();

        foreach($data as $row) {
            $record = array();
            foreach ($row as $field => $value) {
                $record[$field] = $value;
            }
            array_push($result, $record);
        }

        // read rows into an array, keyed to headers
        return $result;
    }

    /**
     * readValidatedFile() reads a file into an array after validating it
     * @param string $path is the path to the file
     * @param string $test is the first expected array key
     * @return array of file contents
     * @throws \Exception if unable to open file
     */
    private static function readValidatedFile(string $path, string $test):array
    {
        // open and decode file into an array
        $file = file_get_contents($path);
        $result = json_decode($file, true);

        // check whether array contains expected key
        if ( ! isset($result[$test])) {
            throw new \Exception('File '.$path.' does not contain array with key '.$test);
        }

        return $result;
    }

    /**
     * @param string $page_id
     * @param array $page_map
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private static function parseData(string $page_id, array $page_map, array $data): array
    {
        // create array for result
        $result = array();

        //pull in configurations
        $sort_field = $data['sort'];
        $group_field = $data['group_by']['field'];
        $delim = $data['group_by']['delim'];
        $records = $data['records'];

        //for the debug version of the page
        if ( $page_id == self::PARSE_DEBUG ) {
            //pull in validation settings from configuration
            $validation_methods = $data['validation'];

            // set page title
            $result['title'] = 'Textbook Report';
            // compile list of all courses across all years
            // so we can add course names to parser
            $groups = array();
            foreach ( $page_map as $key => $page) {
                $groups = array_merge($groups, $page);
            }

            // initialize book list in result
            $result['book_list'] = array();
            // go through list of parser
            foreach ($records as $offset => $record) {
                // check for errors in textbook data
                $invalid_records = array();
                foreach ($record as $label => $value) {

                    // if the field has a validation method defined, validate the value
                    if ( array_key_exists($label, $validation_methods) ) {
                        $valid = self::validateData($value, $validation_methods[$label]);

                        // if the data is invalid, store the record for debugging
                        if ( $valid == false ) {
                            array_push($invalid_records, $record);
                        }
                    }

                }

                // store invalid records in the result array
                $result['invalid'] = $invalid_records;

                // compile list of course names textbook is used for, and append to record
                $record['group_list'] = array();
                $key_array = explode($delim, $record[$group_field]);
                foreach ($key_array as $group_id) {
                    array_push($record['group_list'], $groups[$group_id]);
                }

                // add record to the master book list
                array_push($result['book_list'], $record);
            }

            // sort book_list
            $result['book_list'] = self::bookSort($result['book_list'], $sort_field);

            return $result;
        } else {
            $groups = $page_map;
        }

        // create an array for each course containing the course name and a book list
        foreach ($groups as $group_id => $group_name) {
            $result[$group_id]['group_name'] = $group_name;
            $result[$group_id]['book_list'] = array();
        }

        // for each book that has the course id, append it to the book list
        foreach ($records as $offset => $record) {
            // explode the list of courses for the textbook
            $key_array = explode($delim, $record[$group_field]);
            // for each course
            foreach ($key_array as $key) {
                // if the course is on the current page
                if ( array_key_exists($key, $result) ) {
                    array_push($result[$key]['book_list'], $record);
                }
            }
        }

        // sort result array by courses
        usort($result, function($a, $b) {
            return $a <=> $b;
        });

        // sort books in each course
        foreach ($result as $course => $array) {
            $result[$course]['book_list'] = self::bookSort($result[$course]['book_list'], $sort_field);
        }

        return $result;
    }

    /**
     * validateData() validates Textbook data based on a set of available rules
     *
     * @param string $test is the string being validated
     * @param array $rule is the method of validation
     * @return bool is the result of the validation
     * @throws \Exception if $method does not match one of the available methods
     */
    private static function validateData(string $test, array $rule): bool
    {
        $v = new validator();

        $method = $rule['method'];

        if( isset($rule['args']) ) {
            $arg_set = $rule['args'];
            $args = '';
            foreach ($arg_set as $arg) {
                $args .= $arg.", ";
            }
            $arg_list = preg_replace('/, $/', '', $args);
            $result = $v->$method($arg_list)->validate($test);
        } else {
            $result = $v->$method()->validate($test);
        }

        return $result;
    }

    /**
     * bookSort() sorts a list of books by the specified field
     * @param array $book_list
     * @param string $sort_field
     * @return array
     */
    private static function bookSort(array $book_list, string $sort_field): array
    {
        usort($book_list, function($a, $b) use ($sort_field) {
            return $a[$sort_field] <=> $b[$sort_field];
        });

        return $book_list;
    }
}
