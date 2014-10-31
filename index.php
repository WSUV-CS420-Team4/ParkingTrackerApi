<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();
$db = new PDO("mysql:dbname=CS420G4;host={$db_host}", $db_user, $db_pass);

$app->get('/', function () {
    echo 'Nothing to see here';
});

$app->post('/upload', function () {
    //We could store stuff here
});

$app->run();

?>

