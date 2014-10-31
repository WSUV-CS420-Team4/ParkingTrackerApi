<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/', function () {
    echo 'Nothing to see here';
});

$app->post('/upload', function () {
    //We could store stuff here
});

$app->run();

?>

