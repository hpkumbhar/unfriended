<?php 

require 'facebook-php-sdk/src/facebook.php';
require 'appinfo.conf';

$facebook = new Facebook(array(
  'appId'  => APP_ID,
  'secret' => SECRET,
));

?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="style.css">
  <title>Unfriended: See who's been unfriending you!</title>
</head>

<body>

<?php
$maxfile = 8192;
$userdatadir = 'userdata';

$user = $facebook->getUser();


if ($user) {
  try {
      $user_profile = $facebook->api('/me');
      $friend_graph = $facebook->api('/me/friends');
      $filename = $userdatadir . "/" . $user_profile["id"] . ".dat";
      $prev_filename = $userdatadir . "/" . $user_profile["id"] . "-prev.dat";
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

if($user) {
     	$friends = $friend_graph['data'];


      $ids = array();
     	$index = 0;

     	foreach ($friends as $friend) {
     		$ids[$index] = $friend['id'];
     		$index++;
     	}

      //if user has visited before, file will already exist
     	if(file_exists($filename)) {
     		$fh = fopen($filename, 'r+');
     		$old_ids = json_decode(fread($fh, $maxfile));

        //compare new friends list to old friends list
     		$potential_losers = array_diff($old_ids, $ids);
 		    ftruncate($fh, 0);
 			rewind($fh);
 			fwrite($fh, json_encode($ids));

        //check to make sure users actually exist and haven't just deleted their profiles
        $losers = array();
        foreach ($potential_losers as $index => $id) {
          $graph_url = "https://graph.facebook.com/" . $id;
          $graph_return = json_decode(file_get_contents($graph_url));
          if ($graph_return) {
            array_push($losers, $id);
          }
        }

     		if (!$losers) {
     		  echo ("<div id='congrats'>Congrats! You're so cool that no one has unfriended you since last time!</div>");
     		}
     		else {
     			echo ("<div id='new-losers'>The following losers unfriended you since last time:<ul>");

     			foreach ($losers as $index => $id) {
     				$graph_url = "https://graph.facebook.com/" . $id . "?fields=name,picture";
   	  			$loser_info = json_decode(file_get_contents($graph_url));
            $loser_pic = $loser_info->picture->data->url;
   	  			echo('<li><img src="' . $loser_pic . '"> ' . $loser_info->name . '</li>');
     			}
          echo ('</ul></div>');
     		}

        if(file_exists($prev_filename)) {
          $fh = fopen($prev_filename, 'r');
          $prev_losers = json_decode(fread($fh, $maxfile), true);
          echo ("<div id='old-losers'>But don't forget the losers who previously unfriended you:<ul>");

          foreach ($prev_losers as $cur_loser) {
            $graph_url = "https://graph.facebook.com/" . $cur_loser . "?fields=name,picture";
            $loser_info = json_decode(file_get_contents($graph_url));
            $loser_pic = $loser_info->picture->data->url;
            echo('<li><img src="' . $loser_pic . '"> ' . $loser_info->name . '</li>');
          }
          echo ('</ul></div>');
          fclose($fh);

        }


        if($prev_losers || $losers) {
          $fh = fopen($prev_filename, 'w');
          if(!$prev_losers) {
            $prev_losers = $losers;
          } else {
            $prev_losers = array_merge($prev_losers, $losers);
          }

          fwrite($fh, json_encode($prev_losers));
        }
     	}
     	else {
        if (!is_dir($userdatadir)) {
          mkdir($userdatadir, 0777, true);
        }

     		$fh = fopen($filename, 'w');
     		fwrite($fh, json_encode($ids));
     		echo ("This is your first time checking! We'll keep an eye out in case anybody decides to unfriend you!");
     	}
     	
     	fclose($fh);
} else {
  echo('<a href="' . $loginUrl . '">Login using Facebook');
}

?>
</body>

</html>
