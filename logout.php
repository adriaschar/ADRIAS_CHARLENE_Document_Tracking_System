<?php

include('config.php');
include("session.php");

$_SESSION = array();

header('location: '.BASE_URL.'/login.php');
