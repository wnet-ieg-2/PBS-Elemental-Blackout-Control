# PBS-Elemental-Blackout-Control
A basic system to schedule blacking out PBS-hosted AWS Elemental livestreams

blackout_scheduler.php is designed to be called either from the command line or from cron, invoked by php, like so 
```/usr/bin/php blackout_scheduler.php```

It will read the contents of a schedule.json file in the same directory.  There is a sample version named schedule.json.sample in this repository.

The schedule.json file is expected to include a channel name and a key, both of which are obtained from PBS.  These are the credentials for sending requests to PBS's Local Live Streaming "content-restriction" endpoint.
The other data in the schedule.json file would be one or more "blackout" periods. The sample file shows the format for those.

If invoked by cron -- typically once per minute -- blackout_scheduler.php will:

* Read the "blackout_status_STATIONNAME" file to see if the last reported status of the livestream was "playing" or "blacked_out" -- or is known at all.
* Then, the script reads through the list of "blackout" periods to see if the livestream is supposed to currently be blacked out.
* If the last reported status does not match the desired status -- or if the last reported status is unknown -- the script will send a request to the content-restriction endpoint to make (or confirm) the desired content-restriction status.  
The script will then update the "blackout_status_STATIONNAME" file with the status indicated by PBS's endpoint.

Note that the directory these files are in must be writable by the user running the script, in order to update the "blackout_status_STATIONNAME" file(s).



