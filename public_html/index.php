<?php

header("Access-Control-Allow-Methods: OPTIONS, GET, POST, DELETE, PUT");

require __DIR__ .'/../data/app/bootstrap.php';

$app->run();
