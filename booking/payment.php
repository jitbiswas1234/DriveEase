<?php
// 1. ALWAYS START SESSION AND DB FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/database.php");
require_once('../config/base_url.php');

// 2. LOGIC: GET BOOKING DATA
if(!isset($_GET['booking_id'])) {
    header("Location: ../user/cars.php");
    exit();
}

$booking_id = mysqli_real_escape_string($conn, $_GET['booking_id']);

$sql = "SELECT b.*, c.car_name, c.brand, c.image
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        WHERE b.id = '$booking_id'";

$result = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);

// 3. SECURITY CHECK
if(!$booking) {
    header("Location: ../user/cars.php");
    exit();
}

// 4. NOW START THE UI (Include Navbar AFTER the logic is done)
include("../includes/navbar.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete Your Booking | DriveEase</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        :root { --primary: #000; --accent: #ff3b3b; --bg: #f8fafc; --text: #1e293b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding-top: 80px; }
        .checkout-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; }
        .car-details-card { background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .car-hero { width: 100%; height: 350px; object-fit: cover; }
        .details-content { padding: 40px; }
        .car-name-title { font-size: 2rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 30px; padding-top: 30px; border-top: 1px solid #f1f5f9; }
        .info-item label { display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 5px; text-transform: uppercase; }
        .info-item span { font-weight: 600; }
        .summary-card { background: white; padding: 30px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; position: sticky; top: 100px; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .total-row { margin-top: 20px; padding-top: 20px; border-top: 2px dashed #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .total-amount { font-size: 1.6rem; font-weight: 800; color: var(--accent); }
        .btn-pay { width: 100%; background: var(--primary); color: white; border: none; padding: 18px; border-radius: 16px; font-size: 1.1rem; font-weight: 700; cursor: pointer; margin-top: 25px; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-pay:hover { background: #222; transform: translateY(-2px); }
        @media (max-width: 992px) { .checkout-container { grid-template-columns: 1fr; } .summary-card { position: static; } }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="car-details-card">
        <img src="../uploads/car_images/<?php echo htmlspecialchars($booking['image']); ?>" class="car-hero">
        <div class="details-content">
            <h1 class="car-name-title"><?php echo htmlspecialchars($booking['car_name']); ?></h1>
            <p style="color: #64748b; font-weight: 500;"><i class="fa fa-car"></i> <?php echo htmlspecialchars($booking['brand']); ?> Premium Fleet</p>

            <div class="info-grid">
                <div class="info-item"><label>Pickup</label><span><?php echo htmlspecialchars($booking['pickup_location']); ?></span></div>
                <div class="info-item"><label>Drop</label><span><?php echo htmlspecialchars($booking['drop_location']); ?></span></div>
                <div class="info-item"><label>Duration</label><span><?php echo $booking['total_days']; ?> Days</span></div>
                <div class="info-item"><label>Trip Dates</label><span><?php echo date('M d', strtotime($booking['pickup_date'])); ?> - <?php echo date('M d, Y', strtotime($booking['return_date'])); ?></span></div>
            </div>
        </div>
    </div>

    <div class="payment-sidebar">
        <div class="summary-card">
            <h3>Price Summary</h3>
            <div class="price-row"><span>Base Rental</span><span>₹<?php echo number_format($booking['total_price']); ?></span></div>
            <div class="price-row"><span>Taxes</span><span style="color: #22c55e;">Included</span></div>
            <div class="total-row"><span style="font-weight:700;">Total</span><span class="total-amount">₹<?php echo number_format($booking['total_price']); ?></span></div>

            <button id="payBtn" class="btn-pay"><i class="fa-solid fa-shield-check"></i> Pay ₹<?php echo number_format($booking['total_price']); ?></button>
            <a href="../user/cars.php" style="display:block; text-align:center; margin-top:20px; color:#64748b; text-decoration:none; font-size:0.9rem;">Cancel Booking</a>
        </div>
    </div>
</div>

<script>
var options = {
    "key": "rzp_test_STui2tERIeLURC",
    "amount": "<?php echo $booking['total_price'] * 100; ?>",
    "currency": "INR",
    "name": "DriveEase",
    "description": "Premium Car Booking",
    "handler": function (response){
        window.location.href="verify_payment.php?payment_id="+response.razorpay_payment_id+"&booking_id=<?php echo $booking['id']; ?>";
    },
    "theme": { "color":"#000000" }
};
var rzp = new Razorpay(options);
document.getElementById('payBtn').onclick = function(e){
    this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Initializing...';
    rzp.open();
    e.preventDefault();
}
</script>

<?php include("../includes/footer.php"); ?>
</body>
</html>


<!DOCTYPE html>
<html>

<head>

<title>Payment</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>

body{
font-family:Inter;
background:#f6f7fb;
margin:0;
}

.container{
width:90%;
max-width:900px;
margin:auto;
margin-top:40px;
}

.card{
background:white;
padding:30px;
border-radius:20px;
box-shadow:0 15px 40px rgba(0,0,0,0.08);
}

.car-img{
width:100%;
height:260px;
object-fit:cover;
border-radius:15px;
}

.summary{
margin-top:20px;
line-height:28px;
font-size:15px;
}

.price{
font-size:24px;
font-weight:700;
margin-top:20px;
}

.btn{
margin-top:25px;
padding:15px;
width:100%;
border:none;
border-radius:12px;
background:black;
color:white;
font-weight:600;
font-size:16px;
cursor:pointer;
}

.btn:hover{
background:#222;
}

.back{
margin-top:10px;
display:block;
text-align:center;
text-decoration:none;
color:black;
}

</style>

</head>

<body>

<?php include("../includes/navbar.php"); ?>

<div class="container">

<div class="card">

<img src="../uploads/car_images/<?php echo htmlspecialchars($booking['image']); ?>" class="car-img">

<h2><?php echo htmlspecialchars($booking['car_name']); ?></h2>

<div class="summary">

<p><b>Brand :</b> <?php echo htmlspecialchars($booking['brand']); ?></p>

<p><b>Pickup :</b> <?php echo htmlspecialchars($booking['pickup_location']); ?></p>

<p><b>Drop :</b> <?php echo htmlspecialchars($booking['drop_location']); ?></p>

<p><b>Pickup Date :</b> <?php echo $booking['pickup_date']; ?></p>

<p><b>Return Date :</b> <?php echo $booking['return_date']; ?></p>

<p><b>Total Days :</b> <?php echo $booking['total_days']; ?></p>

</div>

<div class="price">
Total Amount : ₹<?php echo number_format($booking['total_price']); ?>
</div>

<button id="payBtn" class="btn">
Pay Now
</button>

<a href="../cars.php" class="back">
Back to Cars
</a>

</div>

</div>

<script>

var options = {

"key": "rzp_test_STui2tERIeLURC",

"amount": "<?php echo $booking['total_price']*100; ?>",

"currency": "INR",

"name": "DriveEase",

"description": "Car Booking Payment",

"handler": function (response){

window.location.href="verify_payment.php?payment_id="
+response.razorpay_payment_id+
"&booking_id=<?php echo $booking['id']; ?>";

},

"modal": {
    "ondismiss": function(){
        // Handle when user closes the payment modal
        console.log('Payment modal dismissed');
    }
},

"theme":{
"color":"#000"
}

};

var rzp=new Razorpay(options);

document.getElementById('payBtn').onclick=function(e){

e.preventDefault();

// Disable button to prevent multiple clicks
this.disabled = true;
this.innerHTML = 'Processing...';

// Add loading state
this.style.opacity = '0.7';

try {
    rzp.open();
} catch (error) {
    console.error('Payment error:', error);
    this.disabled = false;
    this.innerHTML = 'Pay Now';
    this.style.opacity = '1';

    // Show error message
    alert('Payment service is temporarily busy. Please try again in a few minutes.');
}

}

</script>

<?php include("../includes/footer.php"); ?>

</body>

</html>