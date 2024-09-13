<?php 
    session_start();

    //connect to database
    $conn = mysqli_connect("localhost", "root", "", "wartungv3", 3308);

    if (!$conn) {
        die("Error connecting to database: " . mysqli_connect_error());

        // define global constraints
        define ('ROOT_PATH', realpath(dirname(__FILE__)));
        define ('BASE_URL', 'http://localhost/wartungv3/');
    }