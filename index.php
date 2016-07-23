<?php
  echo "\n##### HTTP_PROXY Env #####\n";
  var_dump($_SERVER['HTTP_PROXY']);
  putenv('HTTP_PROXY=');
  var_dump(getenv('HTTP_PROXY'));

  echo "\n\n##### HTTP Access to http://ifconfig.co #####\n";
  require 'vendor/autoload.php';
  $client = new \GuzzleHttp\Client();
  $response = $client->request('GET', 'http://ifconfig.co', ['debug' => true, 'headers' => ['User-Agent' => 'curl/7.43.0']]);

  echo "\n\n##### Response body #####\n";
  echo $response->getBody();
?>
