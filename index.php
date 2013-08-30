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
  <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
</head>

<body>
<?php

$user = $facebook->getUser();

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($user) {
  try {
      $user_profile = $facebook->api('/me');
      $user_id = $user_profile['id'];
      $_SESSION['uid'] = $user_id;
      $friend_graph = $facebook->api('/me/friends');
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

if ($user) {
         $friends = $friend_graph['data'];

        $res = $mysqli->query("SELECT friend_id FROM friends WHERE user_id = $user_id");

        $ids = array();
         $index = 0;

         foreach ($friends as $friend) {
             $ids[$index] = $friend['id'];
             $index++;
         }

        //if user has visited before, records will already exist
         if ($res->num_rows > 0) {
            $old_ids = array();
            while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                $old_ids[] = $row['friend_id'];
            }

        //compare new friends list to old friends list
             $potential_losers = array_diff($old_ids, $ids);
             $new_friends = array_diff($ids, $old_ids);

            foreach ($new_friends as $index => $id) {
                $mysqli->query("INSERT INTO friends VALUES ($user_id, $id)");
            }

        //check to make sure users actually exist and haven't just deleted their profiles
        $losers = array();
        foreach ($potential_losers as $index => $id) {
          $mysqli->query("DELETE FROM friends WHERE user_id = $user_id AND friend_id = $id");
          $graph_url = "https://graph.facebook.com/" . $id;
          $graph_return = json_decode(file_get_contents($graph_url));
          if ($graph_return) {
            array_push($losers, $id);
          }
        }

             if (!$losers) {
               echo ("<div id='congrats'>Congrats! You're so cool that no one has unfriended you since last time!</div>");
             } else {
                 echo ("<div id='new-losers'>The following losers unfriended you since last time (if you were the one that deleted them, then just close the box and they will not be counted):<ul>");

                 foreach ($losers as $index => $id) {
                     $graph_url = "https://graph.facebook.com/" . $id . "?fields=name,picture";
                     $loser_info = json_decode(file_get_contents($graph_url));
            $loser_pic = $loser_info->picture->data->url;
                     echo('<li id="' . $id . '"><img src="' . $loser_pic . '"> ' . $loser_info->name . '<div class="closeX">&#10006</div></li>');
                 }
          echo ('</ul></div>');
             }

        $res = $mysqli->query("SELECT friend_id FROM friends WHERE user_id = -$user_id");

        if ($res->num_rows > 0) {
          $prev_losers = array();
          while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $prev_losers[] = $row['friend_id'];
          }
          echo ("<div id='old-losers'>But don't forget the losers who previously unfriended you:<ul id='oldLosers'>");

          foreach ($prev_losers as $cur_loser) {
            $graph_url = "https://graph.facebook.com/" . $cur_loser . "?fields=name,picture";
            $loser_info = json_decode(file_get_contents($graph_url));
            $loser_pic = $loser_info->picture->data->url;
            echo('<li id="' . $cur_loser . '"><img src="' . $loser_pic . '"> ' . $loser_info->name . '<div class="closeX">&#10006</div></li>');
          }
          echo ('</ul></div>');
        }

        if ($losers) {
            $mysqli->query("INSERT INTO friends VALUES (-$user_id, $id)");
        }
         } else {
            foreach ($ids as $cur_id) {
                $mysqli->query("INSERT INTO friends VALUES ($user_id, $cur_id)");
            }
             echo ("This is your first time checking! We'll keep an eye out in case anybody decides to unfriend you!");
         }

} else {
    echo("<div id='intro'><h1>Unfriended:</h1><h2>See who's been unfriending you!</h2><p>Unfriended tracks which of your so-called friends have recently unfriended you! Sign in below to get started!</p><a href=$loginUrl><img src='images/facebook_signin.png'></a></div>");

    }

?>

<script src="my.js" type="text/javascript"></script>
</body>

</html>
