<?php

function dbconnect() {
  $dbserver = "localhost";
  $dbname = "CMS_001";
  $dbuser = "blogger";
  $dbpass = "bloggoPLAN";

  $conn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  return $conn;

}

?>
