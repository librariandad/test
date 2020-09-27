<?php
/**
 * This file is part of the Medlib\Parser component.
 *
 * @fileName RecordsParser.php
 * @version 0.1
 * @author Keith Engwall <engwall@oakland.edu>
 * @copyright (c) Oakland University William Beaumont (OUWB) Medical Library
 * @license MIT
 * 
 * @expects a json configuration file (default path: __DIR__."/config.json.example")
 * @expects a json page map file containing a top level key {'pages': 
 * @expects a CSV spreadsheet of records with a header row of field names
 * 
 * This class contains the static method parseRecords(), which will parse a
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
 * This component is used to parse the textbooks spreadsheet maintained 
 * by the OUWB Medical Library into textbook lists for each course in each
 * year of the medical school curriculum.
 */

declare(strict_types=1);

namespace Medlib\Parser;

/**
 * @className RecordsParser
 * @package Medlib\Textbooks
 * @implements RendererInterface
 */
class RecordsParser implements RecordsParserInterface
{
    const CONFIG_PATH = __DIR__ . '/config.json'; // configuration file
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
        $pageID = filter_var($pageIdRaw, FILTER_SANITIZE_STRING);
        $config_path = filter_var($configPathRaw, FILTER_SANITIZE_STRING);

        // set up error handling, read configurations, etc.
        $config = Bootstrap::bootstrap($config_path);

        // get paths for records and page map data
        $basePath = $config['paths']['base_path'];

        // get pageMap data
        $mapPath = $basePath.$config['paths']['page_map'];
        $pageMap = self::getPageMap($mapPath);
        
        // get record data
        $dataPath = $basePath.$config['paths']['record_data'];
        $recordData = self::getRecordData($dataPath, $config);

        // parse the record data according to the page map for the page specified by $pageID
        return self::parseData($pageID, $pageMap, $recordData);
    }

    /**
     * getPageMap() checks the path for a json file containing top level element 'pages'.
     * 
     * @param string $path is the file path
     * @return array is an array of the contents of the 'pages' element
     * @throws \Exception if top level 'pages' is missing
     */
    private static function getPageMap(string $path): array
    {
        // load page map data for courses
        $page_data = FileReader::readJSON($path, 'pages');
        
        return $page_data['pages'];
    }

    /**
     * getRecordData() checks the path for a CSV file of records
     * @param string $path
     * @param array $config
     * @return array
     * @throws \Exception
     */
    private static function getRecordData(string $path, array $config): array
    {
        $recordData = array();
        // get the textbook data records
        $recordData['records'] = FileReader::readCSV($path, $config['group_by'], $config['sort_field']);
        // get the validation methods for the data
        $recordData['validation'] = $config['validation'];
        // get the field data will be grouped by from the config file
        $recordData['group_by'] = $config['group_by'];
        // get the field data will be sorted by from the config file
        $recordData['sort'] = $config['sort_field'];

        return $recordData;
    }
    
    /**
     * @param string $pageID
     * @param array $pageMap
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private static function parseData(string $pageID, array $pageMap, array $data): array
    {
        // create array for result
        $result = array();

        //pull in configurations
        $sort_field = $data['sort'];
        $group_field = $data['group_by']['field'];
        $delim = $data['group_by']['delim'];
        $records = $data['records'];

        // If the page argument passed is the debug keyword, provide debug report.
        //
        // The debug report will provide a single list of all records with record data,
        // including the set of groups under which the record is listed.
        //
        // The debug report will also provide a list of records that fail validation
        // so that they may be checked.
        //
        if ( $pageID == self::PARSE_DEBUG ) {
            
            // validate the record data and store invalid records for the report
            $validation_rules = $data['validation'];
            $invalid_records = self::validate($records, $validation_rules);
            $result['invalid'] = self::bookSort($invalid_records, $sort_field);
            
            // compile list of all listing groups in page map
            $groups = array();
            foreach ( $pageMap as $key => $page) {
                $groups = array_merge($groups, $page);
            }

            // initialize record list in result
            $result['record_list'] = array();
            // go through list of parser
            foreach ($records as $offset => $record) {
                
                // compile list of listing group names, keyed on the group id, for display
                $record['group_list'] = array();
                $key_array = explode($delim, $record[$group_field]);
                foreach ($key_array as $group_id) {
                    array_push($record['group_list'], $groups[$group_id]);
                }

                // add record to the record list
                array_push($result['record_list'], $record);
            }

            // sort record_list
            $result['record_list'] = self::bookSort($result['record_list'], $sort_field);

        } elseif ( array_key_exists($pageID, $pageMap) ) {
            // render requested page
            $groups = $pageMap;
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
        } else {
            // if the page ID is not in the page map then throw an exception
            throw new \Exception($pageID." not in page map.");
        }
        
        return $result;
    }
    
    private static function validate($records, $validation_rules): array
    {
        
        // TODO: add validation failure data to result
        
        // initialize return array
        $result = array();

        // for each book in the file
        foreach ($records as $offset => $record) {

            foreach ($record as $label => $value) {

                // if the field has a validation method defined, validate the value
                if (array_key_exists($label, $validation_rules)) {
                    $valid = Validator::validateData($value, $validation_rules[$label]);

                    // if the data is invalid, store the record for debugging
                    if ($valid == false) {
                        array_push($invalid_records, $record);
                    }
                }

            }
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
