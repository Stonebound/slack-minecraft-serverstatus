<?php
require 'MCQuery.php';

# Add server as a array to add them to the /serverstatus command
$servers = [
    "hub" => [ #servername
        "label" => "Hub", #label
        "port" => "24460", #queryport
        "ppath" => "", #optional tickprofiler data path
    ],
    "dw20" => [
        "label" => "Direwolf20", 
        "port" => "24461",
        "ppath" => "",
    ],
    "inflite" => [
        "label" => "SkyFactory 3",
        "port" => "24462",
        "ppath" => "servers/server8/profile.txt",
    ],
    "infinity" => [
        "label" => "Infinity Expert",
        "port" => "25567",
        "ppath" => "",
    ],
    "test" => [
        "label" => "Test",
        "port" => "25569",
        "ppath" => "",
    ],
];


# Grab some of the values from the slash command, create vars for post back to Slack
$text = $_POST['text'];
$token = $_POST['token'];
$reply = "";

# Check the token and make sure the request is from our group
if($token != 'ADDYOURWEBHOOKTOKENHERE'){
  $msg = "The token for the slash command doesn't match. Check your script.";
  die($msg);
}

# Check if server exists or if checking all
if($text != "all"){
  if(!$servers[$text]){
    $msg = $text . " is not a valid server.";
    die($msg);
  }
}

function serverStatus($label, $port, $ppath)
{
  $status = new MCQuery();
  $response = $status->GetStatus( 'localhost', $port, 1 )->Response();

  if($response["online"] == 1 && $response["error"] == null){
      # Yay, the server is up!
      # Make sure that players are online, if not return nothing
      if ($response["players"] !== 0){
          $playerlist = implode(', ',$response["player_list"]);
      } else {
          $playerlist = "";
      }
      # Get TPS for servers that support it, then send response
      if($ppath != "") {
          $json = file_get_contents($ppath);
          $data = json_decode($json, true);
          $tps = round($data[0]['TPS']);
          
          $localReply = ":green_heart: ".$label." server is online! TPS ".$tps." :thermometer: Players (".$response["players"]."/".$response["max_players"].") ".$playerlist;
      } else {
          $localReply = ":green_heart: ".$label." server is online! Players (".$response["players"]."/".$response["max_players"].") ".$playerlist;
      }
  }else if($response["error"] != null){
      # if no response the server is probably down
      $localReply = ":red_circle: ".$label." server is offline!";
  }
  return $localReply;
};

if($text == "all") {
  # check all servers
  foreach($servers as $server){
    $reply .= serverStatus($server["label"], $server["port"], $server["ppath"])."\n\r";
  }
} else {
  $flabel = $servers[$text]["label"];
  $reply = serverStatus($flabel, $servers[$text]["port"], $servers[$text]["ppath"]);
}

echo $reply;
