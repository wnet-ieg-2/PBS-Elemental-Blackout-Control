<?php

class WNET_PBS_Elemental_Client {
  private $user;
  private $key;
  private $host;

  public function __construct($user = '', $key = '', $host = '') {
    if (!function_exists('curl_init')) {
      die('the curl library is required for this client to work');
    }
    if (empty($user) || empty($key) || empty($host)) {
      die('user, key, and host are required');
    }
    $this->user = $user;
    $this->key = $key;
    $this->host = $host;
  }

  public function enable_elemental_blackout ($channel, $status = false) {
    $ch = curl_init();
    if (!$ch) {
      die('could not initialize curl');
    }
    $path = '/api/v1/channels/' . $channel . '/delivery-restriction';
    $url = $this->host . $path;

    $payload = '{"status": ' . (string)$status . '}'; 

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Skip SSL Verification
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'X-API-KEY: ' . $this->key;
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $errors = curl_error($ch);
    $info = curl_getinfo($ch);
    if (!empty($errors) || !json_decode($result)) {
      echo "errors:" . json_encode($errors) . "\n";
      echo "info" . json_encode($info). "\n";
    }
    curl_close($ch);
    return $result;
  }

}

/* EOF */
