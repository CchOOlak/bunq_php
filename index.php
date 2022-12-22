<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require 'vendor/autoload.php';

function init_db()
{
	$conn = new SQLite3('msn.db');
	// create messages table
	$q = "CREATE TABLE IF NOT EXISTS messages (
            ID INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(10),
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		)";
	$conn->exec($q);
	$conn->close();
}

function save_message($username, $message)
{
	$conn = new SQLite3('msn.db');
	$q = "INSERT INTO messages (username, message) VALUES (:username, :message)";
	$stmt = $conn->prepare($q);
	$stmt->bindValue(":username", $username);
	$stmt->bindValue(":message", $message);
	$stmt->execute();
	$conn->close();
}

function get_messages($cursor)
{
	$conn = new SQLite3('msn.db');
	$q = "SELECT ID, username, message, created_at
		  FROM messages
		  WHERE ID > {$cursor}
		  ORDER BY created_at DESC";
	$q_result = $conn->query($q);

	$result = array();
	if ($q_result) {
		while ($res = $q_result->fetchArray(SQLITE3_ASSOC)) {
			array_push($result, $res);
		}
	}
	$conn->close();

	return $result;
}

init_db();
$app = new Slim\App();

$app->post('/send', function (Request $request, Response $response) {
	$content = json_decode($request->getBody(), TRUE);
	$username = $content["username"] ?? null;
	$message = $content["message"] ?? null;
	if ($username == null || $message == null) {
		$response->getBody()->write("wrong input");
		$response->withStatus(400);
		return $response;
	}
	save_message($username, $message);
	$response->getBody()->write("sent");
	return $response;
});

$app->get('/refresh', function (Request $request, Response $response) {
	// cursor is last message_id that fetched in client side
	$cursor = $request->getQueryParams()["cursor"] ?? 0;
	$messages = get_messages($cursor);
	$response = $response->withHeader("Content-Type", "application/json");
	$response->getBody()->write(json_encode($messages));
	return $response;
});


$app->run();
