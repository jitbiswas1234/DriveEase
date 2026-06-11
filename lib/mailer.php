<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__.'/PHPMailer-master/src/Exception.php';
require_once __DIR__.'/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__.'/PHPMailer-master/src/SMTP.php';

function sendPaymentEmail($email,$name,$booking_id,$amount)
{

$mail = new PHPMailer(true);

try{

$mail->isSMTP();

$mail->Host='smtp.gmail.com';

$mail->SMTPAuth=true;

$mail->Username='bjit4225@gmail.com';

$mail->Password='Ppmjit@1234';

$mail->SMTPSecure='tls';

$mail->Port=587;

$mail->setFrom('bjit4225@gmail.com','Event Booking System');

$mail->addAddress($email,$name);

$mail->isHTML(true);

$mail->Subject='Payment Successful';

$mail->Body="

<h2>Payment Successful</h2>

<p>Hello $name,</p>

<p>Your booking is confirmed.</p>

<p><b>Booking ID:</b> $booking_id</p>

<p><b>Amount Paid:</b> ₹$amount</p>

";

$mail->send();

return true;

}

catch(Exception $e){

return false;

}

}