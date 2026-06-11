<?php
if(session_status()==PHP_SESSION_NONE){
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveX - Premium Car Rental</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #ff4d30;
            --secondary: #010103;
            --gray: #706f7b;
            --light-bg: #f8f9fa;
        }

        body { font-family: 'Poppins', sans-serif; background: #fff; padding-top: 70px; }

        /* Navbar Fix */
        .navbar { box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; }
        .navbar-brand span { color: var(--primary); }

        /* Hero Fix */
        .hero { 
            background: var(--light-bg); 
            padding: 100px 0; 
            min-height: 80vh; 
            display: flex; 
            align-items: center; 
        }
        .hero h1 { font-size: 3.5rem; font-weight: 800; }
        .hero h1 span { color: var(--primary); }

        /* Card Fix */
        .car-card {
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #eee;
            transition: 0.3s;
            margin-bottom: 30px;
        }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .car-img-box { background: #fdfdfd; padding: 20px; text-align: center; }
        .car-img-box img { max-width: 100%; height: 200px; object-fit: contain; }
        
        /* Utility */
        .btn-primary-custom { background: var(--primary); color: #fff; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 600; text-decoration: none; }
        .btn-primary-custom:hover { background: var(--secondary); color: #fff; }
    </style>
</head>
<body>