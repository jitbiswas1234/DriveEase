<?php

session_start();

require_once("../config/database.php");
require_once("../lib/mailer.php");
require_once("../razorpay-php/Razorpay.php");

use Razorpay\Api\Api;

$keyId="rzp_test_STui2tERIeLURC";
$keySecret="J3PWrTq8mVSfAkTlpBj20gLg";

$api=new Api($keyId,$keySecret);

if(isset($_GET['payment_id']) && isset($_GET['booking_id']))
{

$payment_id=mysqli_real_escape_string($conn,$_GET['payment_id']);
$booking_id=mysqli_real_escape_string($conn,$_GET['booking_id']);

try{

/* CAPTURE PAYMENT */
$payment=$api->payment->fetch($payment_id);

$payment->capture([
'amount'=>$payment['amount']
]);

/* Update booking */

$update=mysqli_query($conn,

"UPDATE bookings SET

payment_status='Paid',

booking_status='Confirmed',

payment_id='$payment_id'

WHERE id='$booking_id'");

if($update)
{

$query=mysqli_query($conn,

"SELECT users.name,
users.email,
bookings.total_price,
bookings.booking_code

FROM bookings

JOIN users ON users.id=bookings.user_id

WHERE bookings.id='$booking_id'");

$user=mysqli_fetch_assoc($query);

if($user)
{

sendPaymentEmail(
$user['email'],
$user['name'],
$user['booking_code'],
$user['total_price']
);

}

header("Location:success.php?booking_id=".$booking_id);

exit();

}

}
catch(Exception $e){

header("Location:failed.php");

exit();

}

}
else
{

header("Location:failed.php");

exit();

}

?>