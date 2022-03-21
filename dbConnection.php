<?php
// instantiate database 
$host = 'localhost';
$username ='root';
$password = '';
$charSet = 'UTF-8';
$conn;
$db_name='azora_2db';

try {
       $conn = new PDO('mysql:host=' . $host . ';dbname=' . $db_name , $username, $password , array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
       $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $exception) {
       echo 'Connection Error' . $exception->getMessage();
   }
   return $conn;
?>