<?php
require 'appinfo.conf';

session_start();

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$mysqli->query("DELETE FROM friends WHERE user_id = -" . $_SESSION['uid'] . " AND friend_id =" . $_POST['id']);
