<?php

require __DIR__ . '/vendor/autoload.php';

$config = json_decode(file_get_contents('config.json'));

echo "START \n";

$auth0Api = new \Auth0\SDK\Auth0Api($config->api_token, $config->domain);


$connections = $auth0Api->connections->getAll('auth0');

foreach ($connections as $connection) {
  if ($connection['name'] === 'DB-THETESTBLOG') {
    echo "- DELETED CONNECTION \n";
    $auth0Api->connections->delete($connection['id']);
  } 
}

$clients = $auth0Api->clients->getAll();

foreach ($clients as $client) {
  if ($client['name'] === 'THETESTBLOG') {
    echo "- DELETED CLIENT \n";
    $auth0Api->clients->delete($client['client_id']);
  } 
}

echo "FINISH \n";