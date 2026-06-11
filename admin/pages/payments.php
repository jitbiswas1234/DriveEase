<?php

$status_filter = $_GET['status'] ?? 'all';

$where = "";

if ($status_filter != 'all') {

    $where = " AND b.payment_status='" . mysqli_real_escape_string($conn, $status_filter) . "'";

}


$sql = "SELECT 

b.id,
b.booking_code,
b.total_price,
b.payment_status,
b.payment_id,
b.booking_date,

u.name as customer_name,
u.email as customer_email,

c.car_name

FROM bookings b

LEFT JOIN users u 
ON b.user_id=u.id

LEFT JOIN cars c 
ON b.car_id=c.id

WHERE b.payment_status IS NOT NULL

$where

ORDER BY b.booking_date DESC";

$payments = mysqli_query($conn, $sql);

if (!$payments) {
    die(mysqli_error($conn));
}



/* ===== ADMIN STATS ===== */

$total_revenue = mysqli_fetch_assoc(

    mysqli_query(
        $conn,

        "SELECT SUM(total_price) total 
FROM bookings 
WHERE payment_status='Paid'"
    )

)['total'] ?? 0;


$pending = mysqli_fetch_assoc(

    mysqli_query(
        $conn,

        "SELECT COUNT(*) total 
FROM bookings 
WHERE payment_status='Pending'"
    )

)['total'] ?? 0;


$today = mysqli_fetch_assoc(

    mysqli_query(
        $conn,

        "SELECT SUM(total_price) total 
FROM bookings 
WHERE payment_status='Paid'
AND DATE(booking_date)=CURDATE()"
    )

)['total'] ?? 0;

?>



<div class="payment-stats">

    <div class="payment-stat">

        <div class="icon green">
            <i class="fas fa-rupee-sign"></i>
        </div>

        <h2>₹<?php echo number_format($total_revenue, 2); ?></h2>

        <p>Total Revenue</p>

    </div>



    <div class="payment-stat">

        <div class="icon blue">
            <i class="fas fa-calendar"></i>
        </div>

        <h2>₹<?php echo number_format($today, 2); ?></h2>

        <p>Today's Revenue</p>

    </div>



    <div class="payment-stat">

        <div class="icon orange">
            <i class="fas fa-clock"></i>
        </div>

        <h2><?php echo $pending; ?></h2>

        <p>Pending Payments</p>

    </div>

</div>


<div class="filter-tabs">

<button 
class="filter-btn <?php echo ($status_filter=='all')?'active':'';?>"
onclick="filterPayments('all')">

All

</button>

<button 
class="filter-btn <?php echo ($status_filter=='Paid')?'active':'';?>"
onclick="filterPayments('Paid')">

Paid

</button>

<button 
class="filter-btn <?php echo ($status_filter=='Pending')?'active':'';?>"
onclick="filterPayments('Pending')">

Pending

</button>

</div>



<div class="card">

    <table class="data-table">

        <thead>

            <tr>

                <th>Transaction</th>

                <th>Customer</th>

                <th>Booking</th>

                <th>Amount</th>

                <th>Method</th>

                <th>Status</th>

                <th>Date</th>

            </tr>

        </thead>


        <tbody>

            <?php

            if (mysqli_num_rows($payments) > 0) {

                while ($row = mysqli_fetch_assoc($payments)) {

                    ?>

                    <tr>


                        <td class="txn">

                            <?php

                            echo $row['payment_id']

                                ? substr($row['payment_id'], 0, 14)

                                : 'TXN' . str_pad($row['id'], 6, '0', STR_PAD_LEFT);

                            ?>

                        </td>



                        <td>

                            <b><?php echo $row['customer_name']; ?></b>

                            <br>

                            <span class="small">

                                <?php echo $row['customer_email']; ?>

                            </span>

                        </td>



                        <td>

                            <b>#<?php echo $row['booking_code']; ?></b>

                            <br>

                            <span class="small">

                                <?php echo $row['car_name']; ?>

                            </span>

                        </td>



                        <td class="amount">

                            ₹<?php echo number_format($row['total_price'], 2); ?>

                        </td>



                        <td>

                            Razorpay

                        </td>



                        <td>

                            <span class="badge <?php echo strtolower($row['payment_status']); ?>">

                                <?php echo $row['payment_status']; ?>

                            </span>

                        </td>



                        <td>

                            <?php

                            echo date(

                                "M d Y h:i A",

                                strtotime($row['booking_date'])

                            );

                            ?>

                        </td>


                    </tr>

                    <?php

                }

            } else {

                echo "<tr>
<td colspan='7' class='empty'>

<i class='fas fa-credit-card'></i>

No payments found

</td>
</tr>";

            }

            ?>

        </tbody>

    </table>

</div>



<style>
    .payment-stats {

        display: grid;

        grid-template-columns: repeat(3, 1fr);

        gap: 20px;

        margin-bottom: 25px;

    }


    .payment-stat {

        background: white;

        padding: 20px;

        border-radius: 10px;

        border: 1px solid #eee;

    }


    .icon {

        width: 40px;

        height: 40px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 8px;

        margin-bottom: 10px;

    }


    .green {

        background: #dcfce7;

        color: #166534;

    }

    .blue {

        background: #dbeafe;

        color: #1e40af;

    }

    .orange {

        background: #fef3c7;

        color: #92400e;

    }


    .data-table {

        width: 100%;

        border-collapse: collapse;

        background: white;

    }


    .data-table th {

        text-align: left;

        padding: 15px;

        border-bottom: 1px solid #eee;

    }


    .data-table td {

        padding: 15px;

        border-bottom: 1px solid #f3f3f3;

    }


    .amount {

        color: green;

        font-weight: 700;

    }


    .badge {

        padding: 5px 12px;

        border-radius: 20px;

        font-size: 12px;

        font-weight: 600;

    }


    .badge.paid {

        background: #dcfce7;

        color: #166534;

    }


    .badge.pending {

        background: #fef3c7;

        color: #92400e;

    }


    .small {

        font-size: 12px;

        color: #777;

    }


    .txn {

        font-family: monospace;

        font-weight: 600;

    }


    .empty {

        text-align: center;

        padding: 40px;

        color: #777;


    }
    .filter-tabs{

display:flex;

gap:10px;

margin:20px 0;

}

.filter-btn{

border:none;

padding:8px 18px;

border-radius:8px;

background:#f3f4f6;

color:#333;

font-weight:600;

cursor:pointer;

transition:.3s;

}

.filter-btn:hover{

background:#e5e7eb;

}

.filter-btn.active{

background:black;

color:white;

}
</style>



<script>

    function filterPayments(status) {

        let url = new URL(window.location);

        url.searchParams.set('status', status);

        window.location = url;

    }

</script>