<?php
/**
 * Copyright (c) 2017 University of Texas at Austin
 */

require_once 'vendor/autoload.php';

set_include_path(realpath(dirname(dirname(__FILE__))) . PATH_SEPARATOR . get_include_path());

define('CONFIG_FILE', realpath(dirname(__FILE__)) . '/' . 'config.ini');
define('TEST_FILE', realpath(dirname(__FILE__)) . '/' . 'test_file.txt');
define('TEST_FILE_MODIFIED', realpath(dirname(__FILE__)) . '/' . 'test_file_modified.txt');
