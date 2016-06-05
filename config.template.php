<?php
class Config {
  public static $base = 'http://lithograph.dev/';

  public static $ssl = false;
  public static $secretKey = '000000000000000000000000000000000000000000000';

  public static $clientID = 'http://lithograph.dev/';
  public static $defaultAuthorizationEndpoint = 'https://indieauth.com/auth';

  public static $errbitKey = '';
  public static $errbitHost = '';

  public static $db = [
    'host' => '127.0.0.1',
    'database' => 'lithograph',
    'username' => 'root',
    'password' => ''
  ];
}
