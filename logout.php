<?php 
//Destroir o login
session_start(); 
session_destroy(); 
header('Location: login.php');
?>