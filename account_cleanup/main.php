<?php

require __DIR__ . '/vendor/autoload.php';

$config = json_decode(file_get_contents('config.json'));

echo "START \n";

$connections = \Auth0\SDK\API\ApiConnections::getAll($config->domain, $config->api_token, 'auth0');

foreach ($connections as $connection) {
  if ($connection['name'] === 'DB-THETESTBLOG') {
    echo "- DELETED CONNECTION \n";
    \Auth0\SDK\API\ApiConnections::delete($config->domain, $config->api_token, $connection['id']);
  } 
}

$clients = \Auth0\SDK\API\ApiClients::getAll($config->domain, $config->api_token);

foreach ($clients as $client) {
  if ($client['name'] === 'THETESTBLOG') {
    echo "- DELETED CLIENT \n";
    \Auth0\SDK\API\ApiClients::delete($config->domain, $config->api_token, $client['client_id']);
  } 
}

echo "FINISH \n";