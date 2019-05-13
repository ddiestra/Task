<?php

/**
 * @file
 * Entry File.
 */

// Load all dependencies.
foreach (glob("src/*.php") as $filename) {
  include $filename;
}

use Task\App;
use Task\Model;

if ($config['debug']) {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
}

// Run Application.
$model = new Model($config['database']);
$app = new App($model);
$params = json_decode(file_get_contents('php://input'), TRUE);
$params = $params ?: [];
$params = array_merge($params, $_GET);
$app->run($params);
