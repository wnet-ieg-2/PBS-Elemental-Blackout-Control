<?php

// required client file
$classfile =  __DIR__ . "/class_pbs_elemental_client.php";
require($classfile);

// schedule file
$schedule_filename = __DIR__ . "/schedule.json";
$schedule_file = fopen($schedule_filename, 'r');
$schedule = fread($schedule_file, filesize($schedule_filename));
$channels = json_decode($schedule, TRUE);
if (empty($channels)) {
  die("no json_decodable schedule found at $schedule_filename");
}

$tz = 'America/New_York';
$date = new DateTime('now', new DateTimeZone($tz));
$dayname = $date->format('l');
$ymd = $date->format('Y-m-d');
$miltime = $date->format('Hi');

$default_blackout_status = "playing"; 

$channels = json_decode($schedule, TRUE);

foreach($channels as $channel_name => $channel) {
  $desired_status = $default_blackout_status; // 
  if (!empty($channel['blackouts']) && !empty($channel['user']) && !empty($channel['key']) && !empty($channel['host']) ) {
    $client = new WNET_PBS_Elemental_Client($channel['user'], $channel['key'], $channel['host']);
    $status_filename = __DIR__ . "/blackout_status_" . $channel_name;
    $status_file = fopen($status_filename, 'c+b');
    $fsize = filesize($status_filename);
    $last_status = '';
    if ($fsize) {
      $last_status = trim(fread($status_file, filesize($status_filename)));
    }
    foreach ($channel['blackouts'] as $bdate => $blackoutlist) {
      if ($bdate != $ymd) {
        continue;
      }
      if (!empty($blackoutlist)) {
        foreach ($blackoutlist as $blackout) {
          if ($miltime >= (int) $blackout['start'] && $miltime <= (int) $blackout['end']) {
            //echo "its $miltime and found blackout for $channel_name between " . $blackout['start'] . " and " . $blackout['end'] . " \n";
            $desired_status = "blacked_out";
            break; // don't need to check later blackouts
          }
        }
      }
    }
    if ($last_status != $desired_status) {
      echo "livestream status for $channel_name is $last_status and should be $desired_status: ";
      if ($desired_status == "playing") {
        echo "enabling livestream for $channel_name \n";
        $response = $client->enable_elemental_blackout($channel['user'], 'false');
      } else {
        echo "enabling blackout for $channel_name \n";
        $response = $client->enable_elemental_blackout($channel['user'], 'true');
      }
      if (!json_decode($response)) {
        echo "bad JSON returned from " . $channel['user'] . "\n";
        continue;
      }
      $status_ary = json_decode($response, true);
      $blackout_status = !empty($status_ary['status']) ? "blacked_out" : "playing";
      ftruncate($status_file, 0);
      rewind($status_file);
      fwrite($status_file, $blackout_status);
    }
    fclose($status_file);
  }
}
/* EOF */
