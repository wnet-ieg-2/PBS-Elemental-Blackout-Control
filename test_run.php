<?php
echo "This script reads the current schedule.json file and confirms the elementals respond to having the livestream enabled.\n";
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

$channels = json_decode($schedule, TRUE);
if (!empty($channels)) {
  echo "Successfully read schedule.json file\n";
}
foreach($channels as $channel_name => $channel) {
  if (!empty($channel['blackouts']) && !empty($channel['user']) && !empty($channel['key']) && !empty($channel['host']) ) {
    $client = new WNET_PBS_Elemental_Client($channel['user'], $channel['key'], $channel['host']);
    $response = $client->enable_elemental_blackout($channel['user'], 'false');
    if (!json_decode($response)) {
      echo "bad JSON returned from " . $channel['user'] . "\n";
      continue;
    }
    $status_ary = json_decode($response, true);
    $status = !empty($status_ary['status']) ? "true" : "false";
    
    echo "Configured livestream found on " . $channel['host'] ." for $channel_name with status content-restriction $status\n";
  } 
}
/* EOF */
