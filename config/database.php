<?php

$host="localhost";

$user="root";

$pass="";

$db="car_rental";

$conn=mysqli_connect($host,$user,$pass,$db);

if(!$conn){

die("Database failed");

}

?>