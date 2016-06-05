<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('UTC');

if(getenv('ENV')) {
  require(dirname(__FILE__).'/../config.'.getenv('ENV').'.php');
} else {
  require(dirname(__FILE__).'/../config.php');
}

function initdb() {
  ORM::configure('mysql:host=' . Config::$db['host'] . ';dbname=' . Config::$db['database']);
  ORM::configure('username', Config::$db['username']);
  ORM::configure('password', Config::$db['password']);
}

function logger() {
  static $log;
  if(!isset($log)) {
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler(dirname(__FILE__).'/../logs/lithograph.log', Logger::DEBUG));
  }
  return $log;
}

function log_info($msg) {
  logger()->addInfo($msg);
}

function log_warning($msg) {
  logger()->addWarning($msg);
}

function view($template, $data=[]) {
  global $templates;
  return $templates->render($template, $data);
}

function q() {
  static $caterpillar = false;
  if(!$caterpillar) {
    $logdir = __DIR__.'/../scripts/logs/';
    $caterpillar = new Caterpillar('lithograph', '127.0.0.1', 11300, $logdir);
  }
  return $caterpillar;
}

function redis() {
  static $client = false;
  if(!$client)
    $client = new Predis\Client('tcp://127.0.0.1:6379');
  return $client;
}

function random_string($len) {
  $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  $str = '';
  $c = strlen($charset)-1;
  for($i=0; $i<$len; $i++) {
    $str .= $charset[mt_rand(0, $c)];
  }
  return $str;
}

// Returns true if $needle is the end of the $haystack
function str_ends_with($haystack, $needle) {
  if($needle == '' || $haystack == '') return false;
  return strpos(strrev($haystack), strrev($needle)) === 0;
}

function display_url($url) {
  return preg_replace(['/^https?:\/\//','/\/$/'], '', $url);
}

function session($k, $default=null) {
  if(!isset($_SESSION)) return $default;
  return array_key_exists($k, $_SESSION) ? $_SESSION[$k] : $default;
}


function micropub_post($endpoint, $params, $access_token) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $access_token,
    'Content-type: application/json'
  ));
  curl_setopt($ch, CURLOPT_POST, true);
  $post = json_encode($params);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);
  $response = curl_exec($ch);
  $error = curl_error($ch);
  $sent_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
  $request = $sent_headers . $post;
  return array(
    'request' => $request,
    'response' => $response,
    'error' => $error,
    'curlinfo' => curl_getinfo($ch)
  );
}

function micropub_media_post($endpoint, $access_token, $file) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $access_token
  ));
  curl_setopt($ch, CURLOPT_POST, true);

  $post = [
    'file' => new CURLFile($file)
  ];

  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  $response = curl_exec($ch);
  $error = curl_error($ch);
  $sent_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);

  return array(
    'response' => $response,
    'error' => $error,
    'curlinfo' => curl_getinfo($ch)
  );
}


function micropub_get($endpoint, $params, $access_token) {
  $url = parse_url($endpoint);
  if(!k($url, 'query')) {
    $url['query'] = http_build_query($params);
  } else {
    $url['query'] .= '&' . http_build_query($params);
  }
  $endpoint = http_build_url($url);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $access_token,
    'Accept: application/json',
  ));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  $data = array();
  if($response) {
    $data = @json_decode($response, true);
  }
  $error = curl_error($ch);
  return array(
    'response' => $response,
    'data' => $data,
    'error' => $error,
    'curlinfo' => curl_getinfo($ch)
  );
}

function get_micropub_config(&$user) {
  $targets = array();

  $r = micropub_get($user->micropub_endpoint, [], $user->access_token);

  if($r['data'] && is_array($r['data']) && array_key_exists('media-endpoint', $r['data'])) {
    $user->micropub_media_endpoint = $r['data']['media-endpoint'];
    $user->save();
  }

  return array(
    'response' => $r
  );
}
