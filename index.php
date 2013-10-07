<?php
require_once 'vendor/autoload.php';
require_once 'UserModel.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// connect to database
dibi::connect(array(
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => 'okno',
    'database' => 'spng3',
));

// route middleware for simple API authentication
function authenticate(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
    $token = $app->getEncryptedCookie('token');
    if (validateUserKey($token) === false) {
      $result = array("status" => "error", "message" => 'Invalid security token.');
      echo json_encode($result);
      $app->stop();
      $app->halt(401);
    }
}

// validate user againts database
function validateUserKey($token) {
  $result = dibi::query('SELECT Count(*) FROM [tokens] WHERE [token] = %s', $token)->fetchSingle();
  if ($result != null)
  {
    return true;
  } else {
    return false;
  }
}

/************ REST API *************/

// user detail
$app->get('/user/:id', 'authenticate', function ($id) use ($app) {

    $user_model = new UserModel();

    $user = $user_model->getUser($id);

    if ( ! $user)
    {
        $res = $app->response();
        $res->status(404);
        $res['Content-Type'] = 'application/json';
        $res->body(json_encode(array(
            'error' => 'User not found'
        )));
    }
    else
    {
        // Basic response
        $response = array(
            'error' => null,
            'result'    =>  array(
                'id'   =>  $user['id'],
                'username' =>  $user['username'],
                'email' =>  $user['email'],
            )
        );

        // Respond
        $res = $app->response();
        $res['Content-Type'] = 'application/json';

        $res->body(json_encode($response));
    }
});

// all users
$app->get('/user/', 'authenticate', function () use ($app) {

    $user_model = new UserModel();

    $users = $user_model->getUsers();
    if ( ! $users)
    {
        $res = $app->response();
        $res->status(404);
        $res['Content-Type'] = 'application/json';
        $res->body(json_encode(array(
            'error' => 'Unknown error'
        )));
    }
    else
    {
        // Basic response
        $response = array(
            'error' => null,
            'result' => $users
        );

        // Respond
        $res = $app->response();
        $res['Content-Type'] = 'application/json';

        $res->body(json_encode($response));
    }
});

// set security token
$app->get('/login/:token', function ($token) use ($app) {    
  try {
    $app->setEncryptedCookie('token', $token, '40 minutes');
    $res = $app->response();
    $response = array(
        'error' => null,
        'result' => "Token set."
    );
  	$res->body(json_encode($response));
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});

$app->run();
?>