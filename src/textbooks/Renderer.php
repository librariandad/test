<?php
/**
 * This file is part of the Medlib\Textbooks component.
 *
 * @fileName Renderer.php
 * @version 0.1
 * @author Keith Engwall <engwall@oakland.edu>
 * @copyright (c) Oakland University William Beaumont (OUWB) Medical Library
 * @license MIT
 *
 * This is the textbook rendering engine for the OUWB Medical Library website.
 * When the render method is passed the identifying string for a specific
 * curriculum year,it will read data from the library's textbook spreadsheet
 * and, using the configured page map, organize it for display as lists of
 * recommended and required textbooks for each course in that year.
 */

declare(strict_types=1);

namespace Medlib\Textbooks;

use League\Csv;
//use Monolog\Logger;
//use Monolog\Handler;
use Respect\Validation\Validator as validator;
use Whoops\Exception\Frame;
use Whoops\Exception\Inspector;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
 * @className Renderer
 * @package Medlib\Textbooks
 * @implements RendererInterface
 */
class Renderer implements RendererInterface
{
    // config.php contains file paths and render settings
    const CONFIG_PATH = __DIR__.'/config.json';

    // when passed the RENDER_DEBUG string, render in debug mode
    const RENDER_DEBUG = 'DEBUG';
//    const DEBUG_LOG_PATH = __DIR__."/../log/renderer.log";
//    const DEBUG_LOG_LEVEL = Logger::DEBUG;
//    const DEBUG_NUM_LOGS = 5;

    // when passed an invalid string, render in error mode
    const RENDER_ERROR = 'ERROR';
    const ERROR_MAP = [
        'title' => 'Sorry! There has been an error!',
        'description' => 'Please contact the webmaster at medref@oakland.edu.
                        We apologize for the inconvenience.'
    ];

    public function __construct() {
        // initialize error handling and format error page
        $this->setupErrorHandling();
    }

    public function setupErrorHandling() {
        $whoops = new Run();
        $handler = new PrettyPageHandler();

        // add details table to error page
        $handler->addDataTableCallback(
            'Details',
            function(Inspector $inspector) {
                $data = array();
                $data['Name'] = $inspector->getExceptionName();
                $data['Message'] = $inspector->getExceptionMessage();
                $exception = $inspector->getException();
                $data['Exception class'] = get_class($exception);
                $data['Exception code'] = $exception->getCode();
                $data['Line'] = $exception->getLine();
                $data['Previous'] = $exception->getPrevious();
                $frames = $inspector->getFrames();
                $data['Frame'] = array();
                foreach ($frames as $frame) {
                    $framedata['Class'] = $frame->getClass();
                    $framedata['Function'] = $frame->getFunction();
                    array_push($data['Frame'], $framedata);
                }
                return $data;
            }
        );
        $whoops->pushHandler($handler);
        $whoops->register();
    }

    /**
     * render() produces sets of lists from textbook data, organized
     * as specified in the configuration file for the requested page
     *
     * @param string $page_id is the page requested by the user
     * @param string $config
     * @return array formatted output
     * @throws \Exception in DEBUG mode as a means of testing the page
     *                    after an update
     */
    public function render(string $page_id="M1", string $config=self::CONFIG_PATH): array
    {
        // read configurations for file paths, sorting, validation, etc.
        $config = $this->readValidatedFile(self::CONFIG_PATH, 'paths');

        // load page map data for courses
        $page_map = $this->readValidatedFile($config['paths']['page_map'], 'pages');
        $page_keys = array_keys($page_map['pages']);

        try {
            // read the textbook data
            $textbook_data['records'] = $this->readCSV($config['paths']['textbook_data']);
            // get the validation methods for the data
            $textbook_data['validation'] = $config['validation'];
            // get the course field from the config file
            $textbook_data['group_by'] = $config['group_by'];
            // get the sort field for the books
            $textbook_data['book_sort'] = $config['book_sort'];
        } catch (\Throwable $t) {
            throw new \Exception("Error appending config data to textbook data: ".$t);
        }

        try {
            // parse the textbook data according for requested page
            if ( $page_id == self::RENDER_DEBUG ) {
                // render page as DEBUG
                $result = $this->parseData($page_id, $page_map['pages'], $textbook_data);
            } elseif ( array_key_exists($page_id, $page_keys) ) {
                // render requested page
                $result = $this->parseData($page_id, $page_map['pages'][$page_id], $textbook_data);
            } else {
                throw new \Exception($page_id." not in page map.");
            }
        } catch (\Throwable $e) {
            throw new \Exception("Error parsing data: ".$e->getMessage());
        }
/*
        // if there has been an error, display an error page
        if ( isset($error_message) ) {
            // if in DEBUG mode, log result
            if ($page_id == self::RENDER_DEBUG) {
                $log = $this->getLog($config);
                $log->error($error_message);
            }

            // render the error page
            $result = $this->parseData(self::RENDER_ERROR, self::ERROR_MAP, []);
        }
*/
        return $result;
    }

    /**
     * getLog() returns a logger for use in DEBUG mode
     * @param array $config
//     * @return Logger
     * @throws \Exception
     */
    /*
    private function getLog(array $config): Logger
    {
        // use configured log file or default
        if ( isset($config['paths']['log']) ) {
            $log_path = $config['paths']['log'];
        } else {
            $log_path = self::DEBUG_LOG_PATH;
        }

        //set handler to rotating daily log file
        $log = new Logger('renderer_log');
        $log->pushHandler(new Handler\RotatingFileHandler(
            $log_path,                       // path to log
            self::DEBUG_NUM_LOGS, // max of 5 daily log files
            self::DEBUG_LOG_LEVEL,   // logging level
            true,                    // bubble
            null,               // log file permissions
            true                  // file locking
        ));
        try {
            $log->info("Started Log Handler");
        } catch ( \Throwable $t) {
            throw new \Exception("Error writing to log file ".$log_path.": ".$t);
        }

        return $log;
    }
    */

    /**
     * readCSV() reads a csv file into an array keyed by column headers
     * @param string $path is the CSV file
     * @return array is the data array
     * @throws Csv\Exception
     */

    private function readCSV(string $path):array
    {
        // open the CSV in read mode
        $reader = Csv\Reader::createFromPath($path, 'r');

        // get the column headers
        $reader->setHeaderOffset(0);
        $reader->getHeaderOffset(); //returns 0

        // read rows into an array, keyed to headers
        return array($reader->getRecords());
    }

    /**
     * readValidatedFile() reads a file into an array after validating it
     * @param string $path is the path to the file
     * @param string $test is the first expected array key
     * @return array of file contents
     * @throws \Exception if unable to open file
     */
    private function readValidatedFile(string $path, string $test):array
    {
        // open and decode file into an array
        try {
            $file = file_get_contents($path);
            $result = json_decode($file, true);
        } catch (\Error $e) {
            throw new \ErrorException("Unable to include ".$path.": ".$e->getMessage());
        }

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
    private function parseData(string $page_id, array $page_map, array $data): array
    {
        $result = array();
        $courses = array();

        try {
            $validation_methods = $data['validation'];
            $book_sort = $data['book_sort'];
            $group_field = $data['group_by']['field'];
            $delim = $data['group_by']['delim'];
        } catch (\Exception $e) {
            throw new \Exception("Error in parseData: Expected array keys do not exist: ".$e);
        }


        // if
        if ( $page_id == self::RENDER_ERROR) {
            // TODO: we may not even need to call this if there was an error
            return [];

        } elseif ( $page_id == self::RENDER_DEBUG ) {
            // set page title
            $result['title'] = 'Textbook Report';
            // compile list of all courses across all years
            // so we can add course names to textbooks
            foreach ( $page_map as $key => $page) {
                array_merge($courses, $page['map']);
            }

            //
            foreach ($data as $offset => $record) {
                // check for errors in textbook data
                $invalid_records = array();
                foreach ($record as $label => $value) {

                    // if the field has a validation method defined
                    if ( array_key_exists($label, $validation_methods) ) {
                        try {
                            $valid = $this->validateData($value, $validation_methods[$label]);
                        } catch (\Exception $e) {
                            // if the method is invalid, the data was not checked, so throw an exception
                            throw new \Exception($e);
                        }

                        // if the data is invalid, store the record for debugging
                        if ( $valid == false ) {
                            array_push($invalid_records, $record);
                        }
                    }

                }

                // compile list of course names textbook is used for, and append to record
                $record['course_list'] = array();
                $key_array = explode($delim, $record[$group_field]);
                foreach ($key_array as $course_id) {
                    array_push($record['course_list'], $courses[$course_id]);
                }

                // add record to the master book list
                if ( isset($result['book_list'][$record[$book_sort]]) ) {
                    array_push($result['book_list'][$record[$book_sort]], $record);
                } else {
                    $result['book_list'][$record[$book_sort]] = array($record);
                }
            }


            // TODO: Sort array
            return $result;



        } else {
            $courses = $page_map['map'];
        }

        // create an array for each course containing the course name and a book list
        foreach ($courses as $course_id => $course_name) {
            $result[$course_id]['course_name'] = $course_name;
            $result[$course_id]['book_list'] = array();
        }

        // for each book that has the course id, append it to the book list
        foreach ($data as $offset => $record) {
            // explode the list of courses for the textbook
            $key_array = explode($delim, $record[$group_field]);
            // for each course
            foreach ($key_array as $key) {
                // if the course is on the current page
                if ( array_key_exists($result, $key) ) {
                    array_push($result[$key]['book_list'], $record);
                }
            }
        }

        // TODO: sort array

        return $result;
    }

    /**
     * validateData() validates Textbook data based on a set of available rules
     *
     * @param string $test is the string being validated
     * @param string $method is the method of validation
     * @return bool is the result of the validation
     * @throws \Exception if $method does not match one of the available methods
     */
    private function validateData(string $test, string $method): bool
    {

        // use the specified validation method
        switch($method)
        {
            case 'string':
                $result = validator::stringVal()->validate($test);
                break;
            case 'isbn':
                $result = validator::isbn()->validate($test);
                break;
            case 'url':
                $result = validator::url()->validate($test);
                break;
            case 'year':
                $result = validator::date("Y")->validate($test);
                break;
            case 'edition':
                $result = validator::regex("/1st|2nd|3rd|[4-9]th|[1-9][0-9]th/")->validate($test);
                break;
            default:
                // throw an exception to indicate that validation did not take place
                throw new \Exception($method." is not an available method to validate ".$test);
        }

        return $result;
    }

}
