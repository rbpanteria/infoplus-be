<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

require '../../vendor/autoload.php';

$app = new \Slim\App;

$app->post('/api/login', function (Request $request, Response $response, array $args) {

	$min 				= getenv("TOKEN_LIFETIME");
	$expire_at 	= strtotime("+{$min} minutes");
	$issued_at  = strtotime("now");
	$token 			= JWT::encode(['data' => array('user_id' => 'sample id'), 'exp' => $expire_at, 'iat' => $issued_at], getenv("JWT_SECRET"), "HS256");

	return $response->withJson(array('token' => $token));
});

$app->add(new \Tuupola\Middleware\JwtAuthentication([
	"path" => ["/", "/api"], 
	"ignore" => ["/api/login"],
	"attribute" => "decoded_token_data",
	"secret" => getenv("JWT_SECRET"),
	"algorithm" => ["HS256"],
	"error" => function ($response, $arguments) {
		$data["status"] = "error";
		$data["message"] = $arguments["message"];
		return $response
			->withHeader("Content-Type", "application/json")
			->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
	}
]));

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->run();