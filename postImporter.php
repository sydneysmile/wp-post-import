<?php

require( dirname(__FILE__) . '/wp-load.php' );
include_once(ABSPATH . 'wp-content/plugins/sydneysmilenews/class-sydneysmilenews.php');

    if(isset($argv[1]) && strlen($argv[1]) > 2) {
        $dataPath = $argv[1];
    } else {
        $dataPath = 'facebook-data.json'; //use the data in the current directory which was downloaded from the link provided
    }
    $json = file_get_contents($dataPath);

    $s = new SydneySmileNews();
    $parsedData = $s->parseJsonData($json);
    if($parsedData == false) {
        $error = $s->getError();
        echo "Error occurred when parsing JSON data file: ".$dataPath ."\n";
        foreach ($error as $e) {
            print $e."\n";
        }
    } else {
        $populated = $s->populatePostData();
        if(!$populated) {
            $error = $s->getError();
            echo "Error occurred when populating post data: \n";
            foreach ($error as $e) {
                print $e."\n";
            }
        } else {
            echo "Post data from ".$dataPath." imported successfully\n";
        }
    }