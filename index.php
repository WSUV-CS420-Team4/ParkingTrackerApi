<?php

require 'vendor/autoload.php';
require 'config.php';

$app = new \Slim\Slim();
$db = new PDO("mysql:dbname={$db_name};host={$db_host}", $db_user, $db_pass);

$app->get('/', function () {
    echo 'Nothing to see here';
});

$app->get('/blockfaces', function () use ($app,$db) {
  //Retrieve all? blockfaces
  //Should probably paginate
  checkSession($app, $db);
  $results = $db->query("SELECT Plate, Block, Face, Stall, Time, GROUP_CONCAT(a.Abbreviation SEPARATOR ',') AS Attr
                          FROM Parking AS p
                          LEFT JOIN ParkingAttributes AS pa ON p.ParkingId=pa.ParkingId
                          LEFT JOIN Attribute AS a ON pa.AttributeId=a.AttributeId
                          GROUP BY Block, Face, Stall
                          ORDER BY Block, Face, Stall");
  $data = array('blockfaces' => $results->fetchAll(PDO::FETCH_OBJ));
  echo json_encode($data, JSON_NUMERIC_CHECK);
});

$app->get('/blockfaces/:id', function($id) use ($app,$db) {
  //Retrieve single blockface
  checkSession($app, $db);
  $stmt = $db->prepare("SELECT Plate, Block, Face, Stall, Time, GROUP_CONCAT(a.Abbreviation SEPARATOR ',') AS Attr
                          FROM Parking AS p
                          LEFT JOIN ParkingAttributes AS pa ON p.ParkingId=pa.ParkingId
                          LEFT JOIN Attribute AS a ON pa.AttributeId=a.AttributeId
                          WHERE p.ParkingId=:id
                          GROUP BY Block, Face, Stall
                          ORDER BY Block, Face, Stall");
  $stmt->execute(array(":id" => $id));
  $stall = $stmt->fetchObject();
  $data = $stall ? $stall : array();
  echo json_encode($data, JSON_NUMERIC_CHECK);
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

        if (count($stall->Attr)) {
          $id = $db->lastInsertId();
          $attrStmt = $db->prepare("INSERT INTO ParkingAttributes (ParkingId, AttributeId) VALUES (:parking, :attr)");
          $attrStmt->bindParam(':parking', $id);
          $attrStmt->bindParam(':attr', $attr);

          foreach ($stall->Attr as $attr) {
            $attrStmt->execute();
          }
        }
        
      } else {
        //empty stall
        
      }
    }
  }
});

$app->get('/stats/usage', function () use ($app, $db) {

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
  
  if (($data === NULL) || (!property_exists($data, 'Username')) || (!property_exists($data, 'Password'))) {
    badRequest($app, "Incorrect parameters given. Username and Password fields expected");
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

      //Keep password formats up to date
      if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        $hash = password_hash($data->Password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE User SET Password=:password WHERE UserId=:userid");
        $stmt->execute(array(":userid" => $row['UserId'], ":password" => $hash));
      }
    } else {
      //Bad password
      $app->response->setStatus(401);
      $data = array("error" => "Failed authentication");
      echo json_encode($data);
      return;
    }
  } else {
    //Bad user
    $app->response->setStatus(401);
    $data = array("error" => "Failed authentication");
    echo json_encode($data);
    return;
  }

});

// User account manipulation

//User account creation
$app->post('/user', function () use ($app, $db) {
  checkSession($app, $db);
  $body = $app->request->getBody();
  $data = json_decode($body);

  if (($data === NULL) || (!property_exists($data, 'Username')) || (!property_exists($data, 'Password'))) {
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


//Update user
$app->put('/user', function () use ($app, $db) {

});

function badRequest($app, $msg=false) {
  $app->response->setStatus(400);
  $data = array("error" => "Bad request");
  if ($msg) {
    $data['msg'] = $msg;
  }
  echo json_encode($data);
  exit();
}

function unauthorizedRequest($app) {
  $app->response->setStatus(401);
  $data = array("error" => "Request not authorized");
  echo json_encode($data);
  exit();  
}

function serverError($app, $msg=false) {
  $app->response->setStatus(500);
  $data = array("error" => "Server error");
  if ($msg) {
    $data['msg'] = $msg;
  }
  echo json_encode($data);
  exit();  
}

define("ROLE_USER", 1);
define("ROLE_ADMIN", 2);

function checkSession($app, $db, $permission=ROLE_USER) {
  $token = $app->request->headers->get('X-Auth-Token');
  if ($token) {
    $stmt = $db->prepare("SELECT ur.RoleId FROM Session AS s
      INNER JOIN UserRoles AS ur ON s.UserId=ur.UserId
      WHERE s.SessionToken = :token");
    $res = $stmt->execute(array(":token" => $token));

    if ($res) {
      foreach($stmt->fetchAll() as $row) {
        $role = $row['RoleId'];
        if ($role == $permission) {
          $update = $db->prepare("UPDATE Session SET LastSeen = NOW() WHERE SessionToken = :token");
          $update->execute(array(":token" => $token));
          return true;
        }
      }
    }
  }
  unauthorizedRequest($app);
  return false;
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

