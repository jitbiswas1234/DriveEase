<?php

session_start();

require_once("config/database.php");

$msg="";

if(isset($_POST['send'])){

$name=mysqli_real_escape_string($conn,$_POST['name']);

$email=mysqli_real_escape_string($conn,$_POST['email']);

$subject=mysqli_real_escape_string($conn,$_POST['subject']);

$message=mysqli_real_escape_string($conn,$_POST['message']);

$sql="INSERT INTO contacts(

name,
email,
subject,
message

)

VALUES(

'$name',
'$email',
'$subject',
'$message'

)";

if(mysqli_query($conn,$sql)){

$msg="Message sent successfully";

}else{

$msg="Error sending message";

}

}

?>

<!DOCTYPE html>

<html>

<head>

<title>Contact | DriveEase</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{

font-family:Inter,Arial;

background:#f8fafc;

margin:0;

}

/* HERO */

.contact-hero{

height:320px;

background:linear-gradient(135deg,#000,#1f2937);

color:white;

display:flex;

align-items:center;

justify-content:center;

flex-direction:column;

position:relative;

overflow:hidden;

}

.contact-hero h1{

font-size:42px;

margin:0;

}

.contact-hero p{

opacity:.7;

margin-top:10px;

}

.hero-shape{

position:absolute;

width:300px;

height:300px;

background:rgba(255,255,255,.05);

border-radius:50%;

top:-80px;

right:-80px;

}

/* MAIN */

.contact-container{

max-width:1250px;

margin:-80px auto 60px;

display:grid;

grid-template-columns:1fr 1fr;

gap:30px;

padding:20px;

position:relative;

z-index:2;

}

.card{

background:white;

padding:35px;

border-radius:18px;

box-shadow:0 20px 60px rgba(0,0,0,.08);

transition:.4s;

}

.card:hover{

transform:translateY(-8px);

}

/* CONTACT INFO */

.contact-info{

margin-bottom:25px;

}

.contact-info div{

margin-bottom:18px;

display:flex;

align-items:center;

gap:15px;

font-weight:500;

}

.contact-info i{

width:45px;

height:45px;

background:black;

color:white;

display:flex;

align-items:center;

justify-content:center;

border-radius:12px;

}

/* STATS */

.contact-stats{

display:grid;

grid-template-columns:repeat(3,1fr);

gap:15px;

margin-top:25px;

}

.stat{

background:#f1f5f9;

padding:18px;

border-radius:12px;

text-align:center;

}

.stat h3{

margin:0;

font-size:22px;

}

.stat p{

margin:5px 0 0;

font-size:13px;

color:#666;

}

/* IMAGE */

.contact-image{

width:100%;

border-radius:14px;

margin-top:25px;

height:220px;

object-fit:cover;

}

/* FORM */

.form-title{

font-size:24px;

margin-bottom:5px;

}

.form-sub{

color:#666;

margin-bottom:25px;

font-size:14px;

}

input,textarea{

width:100%;

padding:15px;

margin-bottom:18px;

border:1px solid #e5e7eb;

border-radius:10px;

transition:.3s;

background:#fafafa;

}

input:focus,
textarea:focus{

border-color:black;

background:white;

outline:none;

}

textarea{

height:150px;

resize:none;

}

button{

background:black;

color:white;

border:none;

padding:16px;

width:100%;

border-radius:10px;

font-weight:600;

cursor:pointer;

transition:.3s;

}

button:hover{

background:#111;

transform:translateY(-2px);

}

.success{

background:#dcfce7;

color:#166534;

padding:14px;

border-radius:10px;

margin-bottom:20px;

}

iframe{

width:100%;

height:260px;

border-radius:12px;

border:none;

margin-top:20px;

}

@media(max-width:900px){

.contact-container{

grid-template-columns:1fr;

}

.contact-stats{

grid-template-columns:1fr;

}

}

</style>

</head>

<body>

<?php include("includes/navbar.php"); ?>

<div class="contact-hero">

<div class="hero-shape"></div>

<h1>Contact DriveEase</h1>

<p>Premium car rental support team ready to help you</p>

</div>

<div class="contact-container">

<!-- LEFT -->

<div class="card">

<h2>Contact Information</h2>

<div class="contact-info">

<div>
<i class="fas fa-map-marker-alt"></i>
Kolkata, India
</div>

<div>
<i class="fas fa-phone"></i>
+91 8101688252
</div>

<div>
<i class="fas fa-envelope"></i>
biswasjit862@gmail.com
</div>

</div>

<div class="contact-stats">

<div class="stat">
<h3>24/7</h3>
<p>Support</p>
</div>

<div class="stat">
<h3>500+</h3>
<p>Bookings</p>
</div>

<div class="stat">
<h3>100+</h3>
<p>Cars</p>
</div>

</div>

<img class="contact-image"
src="https://images.unsplash.com/photo-1494976388531-d1058494cdd8">

<h3 style="margin-top:25px;">Our Location</h3>

<iframe
src="https://www.google.com/maps?q=Kolkata&output=embed">
</iframe>

</div>

<!-- RIGHT -->

<div class="card">

<div class="form-title">
Send Message
</div>

<div class="form-sub">
Our team usually replies within 24 hours
</div>

<?php if($msg): ?>

<div class="success">

<?php echo $msg; ?>

</div>

<?php endif; ?>

<form method="POST">

<input type="text"
name="name"
placeholder="Full Name"
required>

<input type="email"
name="email"
placeholder="Email"
required>

<input type="text"
name="subject"
placeholder="Subject"
required>

<textarea
name="message"
placeholder="Write your message"
required>
</textarea>

<button name="send">

Send Message

</button>

</form>

</div>

</div>

<?php include("includes/footer.php"); ?>

</body>

</html>