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
  checkSession($app, $db);
  $results = $db->query("SELECT Plate, Block, Face, Stall, Time FROM Parking ORDER BY Block, Face, Stall");
  $data = array('blockfaces' => $results->fetchAll(PDO::FETCH_OBJ));
  echo json_encode($data, JSON_NUMERIC_CHECK);
});

$app->get('/blockfaces/:id', function($id) use ($db) {
  //Retrieve single blockface
  checkSession($app, $db);
  $stmt = $db->prepare("SELECT Plate, Block, Face, Stall, Time FROM Parking WHERE ParkingId = :id");
  $stmt->execute(array(":id" => $id));
  echo json_encode($stmt->fetchObject("Stall"), JSON_NUMERIC_CHECK);
});

$app->post('/blockfaces', function () use ($app, $db) {
  //Upload parking data
  checkSession($app, $db);
  $body = $app->request->getBody();
  $data = json_decode($body);
  if ($data === NULL) {
    badRequest($app);
    return;
  }
  $insert = $db->prepare("INSERT INTO Parking (Plate, Block, Face, Stall, Time) VALUES (:plate, :block, :face, :stall, :time)");
  $insert->bindParam(':block', $block);
  $insert->bindParam(':face', $face);
  $insert->bindParam(':plate', $plate);
  $insert->bindParam(':stall', $stallNum);
  $insert->bindParam(':time', $time);

  foreach ($data->blockfaces as $blockface) {
    $block = $blockface->Block;
    $face = $blockface->Face;
    foreach ($blockface->Stalls as $key => $stall) {
      if ((isset($stall->Plate)) && (strlen($stall->Plate) > 0)) {
        $plate = $stall->Plate;
        $stallNum = $key;
        $dt = DateTime::createFromFormat(DateTime::ISO8601, $stall->Time);
        $time = $dt->format("Y-m-d H:i:s");
        $insert->execute();
      } else {
        //empty stall
        
      }
    }
  }
});

$app->get('/streetmodel', function () use ($app, $db) {
  checkSession($app, $db);

  $dt = new DateTime();
  $time = $dt->format(DateTime::ISO8601);

  $results = $db->query("SELECT Block, Face, numStalls FROM Block ORDER BY Block, Face ASC");
    
  if ($results) {
    $data = $results->fetchAll(PDO::FETCH_OBJ);
  } else {
    print_r($db->errorInfo());
    $app->response->setStatus(404);
    return;
  }

  $out = array('blockfaces' => $data, 'epochTime' => $time);

  echo json_encode($out, JSON_NUMERIC_CHECK);
});

//Authentication

$app->post('/login', function() use ($app, $db) {
  $body = $app->request->getBody();
  $data = json_decode($body);

  if (($data === NULL) || (!method_exists($data, 'Username')) || (!method_exists($data, 'Password'))) {
    badRequest($app);
    return;
  }

  //Verify user/password
  $stmt = $db->prepare("SELECT UserId, Password FROM User WHERE Name=:username");
  $results = $stmt->execute(array(":username" => $data->Username));

  if ($results) {
    $row = $stmt->fetch();
    $hash = $row['Password'];
    if (password_verify($data->Password, $hash)) {
      //Create session
      $sessionToken = bin2hex(openssl_random_pseudo_bytes(32));
      $stmt = $db->prepare("INSERT INTO Session (UserId, SessionToken, LastSeen) VALUES (:userid, :token, NOW())");
      $res = $stmt->execute(array(":userid" => $row['UserId'], ":token" => $sessionToken));
      $data = array('Token' => $sessionToken);
      echo json_encode($data);
    } else {
      //Bad password
      $app->response->setStatus(401);
      $data = array("error" => "Failed authentication");
      echo json_encode($data);
      return;
    }
  } else {
    badRequest($app);
    return;
  }

});

// User account manipulation

//User account creation
$app->post('/user', function () use ($app, $db) {
  checkSession($app, $db);
  $body = $app->request->getBody();
  $data = json_decode($body);

  if (($data === NULL) || (!method_exists($data, 'Username')) || (!method_exists($data, 'Password'))) {
    badRequest($app);
    return;
  }

  $stmt = $db->prepare("INSERT INTO User (Name, Password) VALUES (:name, :password)");
  $res = $stmt->execute(array(":name" => $data->Username, ":password" => password_hash($data->Password, PASSWORD_DEFAULT)));

  if (!$res) {

  }

  //Add user role
  $userid = $db->lastInsertId();
  $stmt = $db->prepare("INSERT INTO UserRoles (UserId, RoleId) VALUES (:userid, :roleid)");
  $res = $stmt->execute(array(":userid" => $userid, ":roleid" => ROLE_USER));

  if (!$res) {

  }
});

function badRequest($app) {
  $app->response->setStatus(400);
  $data = array("error" => "Bad request");
  echo json_encode($data);
  exit();
}

function unauthorizedRequest($app) {
  $app->response->setStatus(401);
  $data = array("error" => "Request not authorized");
  echo json_encode($data);
  exit();  
}

define("ROLE_USER", 1);
define("ROLE_ADMIN", 2);

function checkSession($app, $db, $permission=ROLE_USER) {
  $token = $app->request->headers->get('X-Auth-Token');
  if ($token) {
    $stmt = $db->prepare("SELECT ur.RoleId FROM Session AS s INNER JOIN UserRoles AS ur ON s.UserId=ur.UserId WHERE s.SessionToken = :token");
    $res = $stmt->execute(array(":token" => $token));

    if ($res) {
      foreach($stmt->fetchAll() as $row) {
        $role = $row['RoleId'];
        if ($role == $permission) {
          return true;
        }
      }

      unauthorizedRequest($app);
    } else {
      unauthorizedRequest($app);
      return false;
    }
  }

  badRequest($app);
}

//Test endpoints

$app->get('/test/auth', function () use ($app, $db) {
  $data = array();

  if (checkSession($app, $db)) {
    $data['success'] = 1;
  } else {
    $data['success'] = 0;
  }

  echo json_encode($data, JSON_NUMERIC_CHECK);
  return;
});

$app->get('/test/authAdmin', function () use ($app, $db) {
  $data = array();

  if (checkSession($app, $db, ROLE_ADMIN)) {
    $data['success'] = 1;
  } else {
    $data['success'] = 0;
  }

  echo json_encode($data, JSON_NUMERIC_CHECK);
  return;
});

$app->run();

?>
