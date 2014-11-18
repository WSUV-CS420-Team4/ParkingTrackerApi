<?php

require 'vendor/autoload.php';
require 'config.php';

$app = new \Slim\Slim();
$db = new PDO("mysql:dbname={$db_name};host={$db_host}", $db_user, $db_pass);

$app->get('/', function () {
    echo 'Nothing to see here';
});

$app->get('/blockfaces', function () use ($db) {
  //Retrieve all? blockfaces
  //Should probably paginate
  $results = $db->query("SELECT Plate, Block, Face, Stall, Time FROM Parking ORDER BY Block, Face, Stall");
  $data = $results->fetchAll(PDO::FETCH_OBJ);
  echo json_encode($data, JSON_NUMERIC_CHECK);
});

$app->get('/blockfaces/:id', function($id) use ($db) {
  //Retrieve single blockface
  $stmt = $db->prepare("SELECT Plate, Block, Face, Stall, Time FROM Parking WHERE ParkingId = :id");
  $stmt->execute(array(":id" => $id));
  echo json_encode($stmt->fetchObject("Stall"), JSON_NUMERIC_CHECK);
});

$app->post('/blockfaces', function () use ($app, $db) {
    $body = $app->request->getBody();
    $data = json_decode($body);
    if ($data === NULL) {
      $app->response->setStatus(400);
      return;
    }
    $insert = $db->prepare("INSERT INTO Parking (Plate, Block, Face, Stall, Time) VALUES (:plate, :block, :face, :stall, :time)");
    $insert->bindParam(':block', $block);
    $insert->bindParam(':face', $face);
    $insert->bindParam(':plate', $plate);
    $insert->bindParam(':stall', $stallNum);
    $insert->bindParam(':time', $time);

    foreach ($data->blockfaces as $blockface) {
      $block = $blockface->block;
      $face = $blockface->face;
      foreach ($blockface->stalls as $key => $stall) {
        if (strlen($stall->plate) > 0) {
          $plate = $stall->plate;
          $stallNum = $key;
          $dt = DateTime::createFromFormat(DateTime::ISO8601, $stall->time);
          $time = $dt->format("Y-m-d H:i:s");
          $insert->execute();
        } else {
          //empty stall
        }
      }
    }
});

class Stall {
    public $Plate;
    public $Block;
    public $Face;
    public $Stall;
    public $Time;
}
$app->run();

?>
