<?php 
session_start();

// define global constants
define( 'ROOT_PATH', realpath(dirname(__FILE__)));
define( 'BASE_URL', 'http://localhost/hreniucv3/');

// connect to database
$conn = mysqli_connect("localhost", "root", "", "wartungv3", 3308);

if (!$conn) {
    die("Error connecting to database: " . mysqli_connect_error());
}
?>