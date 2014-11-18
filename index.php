<?php

require 'vendor/autoload.php';
require 'config.php';

$app = new \Slim\Slim();
$db = new PDO("mysql:dbname={$db_name};host={$db_host}", $db_user, $db_pass);

$app->get('/', function () {
    echo 'Nothing to see here';
});

$app->get('/blockfaces', function () {
  //Retrieve all? blockfaces
  //Should probably paginate
});

$app->get('/blockfaces/:id', function($id) {
  //Retrieve single blockface
});

$app->post('/blockfaces', function () {
    $body = $app->request->getBody();
    $data = json_decode($body);
    if ($data === NULL) {
      $app->response->setStatus(400);
      return;
    }
    $insert = $db->prepare("INSERT INTO Parking (Plate, Block, Face, Time) VALUES (:plate, :block, :face, :time)");
    $insert->bindParam(':block', $block);
    $insert->bindParam(':face', $face);
    $insert->bindParam(':plate', $plate);
    $insert->bindParam(':time', $time);

    foreach ($data->blockfaces as $blockface) {
      $block = $blockface->block;
      $face = $blockface->face
      foreach ($blockface->stalls as $stall) {
        if (strlen($stall->plate) > 0) {
          $plate = $stall->plate;
          $dt = DateTime::createFromFormat(DateTime::ISO8601, $stall->time);
          $time = $dt->format("Y-m-d H:i:s");
          $insert->execute();
        } else {
          //empty stall
        }
      }
    }
});


$app->run();

?>
