<?php

    function setupConfig(): array
    {
        return array(
            'config.json' => '{
              "paths": {
    "basePath": "/Library/WebServer/Documents/components/test/",
    "recordData": "data/records.csv.example",
    "pageMap": "data/page_map.json.example"
  },
  "groupBy": {
    "field": "Course",
    "delim": "|"
  },
  "sortField": "Author",
  "validation": {
    "Title": {
      "method": "stringVal"
    },
    "Author": {
      "method": "stringVal"
    },
    "Required": {
      "method": "in",
      "args": [
        "Required",
        "Recommended"
      ]
    },
    "ISBN": {
      "method": "regex",
      "args": ["/^97[89][- ]?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[0-9X]$/"]
    },
    "URL": {
      "method": "url"
    },
    "Year": {
      "method": "date",
      "args": ["Y"]
    },
    "Edition": {
      "method": "regex",
      "args": ["/1st|2nd|3rd|[4-9]th|[1-9][0-9]th/"]
    }
  }
            }',
            'empty_config.json' => '{}'
        );
    }

