<?php
session_start();
require_once 'config/database.php';
require_once 'config/base_url.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

// Fetch featured cars
$featuredCarsQuery = "SELECT * FROM cars WHERE status = 'Available' ORDER BY created_at DESC LIMIT 6";
$featuredCarsResult = mysqli_query($conn, $featuredCarsQuery);

// Get statistics
$totalCars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars"))['count'];
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$totalBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Completed'"))['count'];

// Get unique brands for filter
$brandsQuery = "SELECT DISTINCT brand FROM cars ORDER BY brand";
$brandsResult = mysqli_query($conn, $brandsQuery);

// Get car types for filter
$typesQuery = "SELECT DISTINCT car_type FROM cars ORDER BY car_type";
$typesResult = mysqli_query($conn, $typesQuery);

$pageTitle = "Home";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Premium car rental service - Find your perfect ride today">
    <title>DriveEase - Premium Car Rental Service</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* ============ CSS VARIABLES ============ */
        :root {
            --primary: #000000;
            --primary-light: #1a1a1a;
            --secondary: #ffffff;
            --accent: #ff4444;
            --accent-hover: #cc0000;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-300: #d4d4d4;
            --gray-400: #a3a3a3;
            --gray-500: #737373;
            --gray-600: #525252;
            --gray-700: #404040;
            --gray-800: #262626;
            --gray-900: #171717;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --font-primary: 'Inter', sans-serif;
            --font-display: 'Playfair Display', serif;
        }

        /* ============ BASE STYLES ============ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--secondary);
            color: var(--primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /* ============ PRELOADER ============ */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--secondary);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .preloader-logo {
            font-family: var(--font-display);
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2rem;
            animation: pulse 1.5s ease-in-out infinite;
        }

        .preloader-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--gray-200);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ============ HERO SECTION ============ */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 8rem 5% 5rem;
            overflow: hidden;
            background: linear-gradient(180deg, var(--gray-50) 0%, var(--secondary) 100%);
        }

        .hero-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(0,0,0,0.02) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0,0,0,0.02) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-grid-lines {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0,0,0,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-text {
            max-width: 600px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            background: var(--primary);
            color: var(--secondary);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: 4rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .hero-title span {
            position: relative;
            display: inline-block;
        }

        .hero-title span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 12px;
            background: var(--accent);
            opacity: 0.3;
            z-index: -1;
        }

        .hero-description {
            font-size: 1.15rem;
            color: var(--gray-600);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .hero-features {
            display: flex;
            gap: 2rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .hero-feature i {
            width: 24px;
            height: 24px;
            background: var(--gray-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: var(--primary);
        }

        .hero-feature span {
            font-size: 0.9rem;
            color: var(--gray-700);
            font-weight: 500;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--secondary);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .btn-outline {
            background: transparent;
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: var(--secondary);
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            flex-wrap: wrap;
        }

        .hero-stat {
            text-align: left;
        }

        .hero-stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            display: flex;
            align-items: baseline;
        }

        .hero-stat-number span {
            font-size: 1.5rem;
            color: var(--accent);
            margin-left: 2px;
        }

        .hero-stat-label {
            font-size: 0.9rem;
            color: var(--gray-500);
            margin-top: 0.3rem;
        }

        /* Hero Image */
        /* .hero-image {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-car-wrapper {
            position: relative;
            width: 100%;
            max-width: 650px;
        }

        .hero-car-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            background: var(--gray-100);
            border-radius: 50%;
            z-index: 0;
        }

        .hero-car-bg::before {
            content: '';
            position: absolute;
            top: -30px;
            left: -30px;
            right: -30px;
            bottom: -30px;
            border: 2px dashed var(--gray-300);
            border-radius: 50%;
            animation: rotateDashed 30s linear infinite;
        } */

        @keyframes rotateDashed {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .hero-car-image {
            position: relative;
            z-index: 1;
            width: 100%;
            animation: carFloat 4s ease-in-out infinite;
        }

        .hero-car-image img {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 30px 60px rgba(0,0,0,0.3));
            /* NO grayscale filter - keeping images in full color */
        }

        @keyframes carFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .hero-floating-card {
            position: absolute;
            background: var(--secondary);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-xl);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 2;
            animation: floatCard 3s ease-in-out infinite;
        }

        .hero-floating-card-1 {
            top: 10%;
            right: 0;
            animation-delay: 0s;
        }

        .hero-floating-card-2 {
            bottom: 20%;
            left: 0;
            animation-delay: -1.5s;
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .floating-card-icon {
            width: 45px;
            height: 45px;
            background: var(--gray-100);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .floating-card-text h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary);
        }

        .floating-card-text p {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        /* ============ SEARCH BOX ============ */
        .search-section {
            padding: 0 5%;
            margin-top: -4rem;
            position: relative;
            z-index: 10;
        }

        .search-box {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--secondary);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-2xl);
            border: 1px solid var(--gray-200);
        }

        .search-box-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }

        .search-tabs {
            display: flex;
            gap: 0.5rem;
        }

        .search-tab {
            padding: 0.6rem 1.5rem;
            background: var(--gray-100);
            border: none;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gray-600);
            cursor: pointer;
            transition: var(--transition);
        }

        .search-tab.active {
            background: var(--primary);
            color: var(--secondary);
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(4, 1fr) auto;
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--gray-400);
        }

        .form-control {
            padding: 1rem 1.2rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--primary);
            background: var(--gray-50);
            transition: var(--transition);
            outline: none;
            width: 100%;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: var(--primary);
            background: var(--secondary);
            box-shadow: 0 0 0 4px rgba(0,0,0,0.05);
        }

        .search-btn {
            padding: 1rem 2.5rem;
            background: var(--primary);
            color: var(--secondary);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            height: fit-content;
        }

        .search-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* ============ SECTION STYLES ============ */
        .section {
            padding: 6rem 5%;
        }

        .section-container {
            max-width: 1300px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--gray-100);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .section-title span {
            position: relative;
        }

        .section-title span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 10px;
            background: var(--accent);
            opacity: 0.2;
            z-index: -1;
        }

        .section-description {
            font-size: 1.1rem;
            color: var(--gray-500);
            max-width: 600px;
            margin: 0 auto;
        }

        /* ============ FEATURES SECTION ============ */
        .features-section {
            background: var(--secondary);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .feature-card {
            padding: 2.5rem;
            background: var(--secondary);
            border: 2px solid var(--gray-100);
            border-radius: 20px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover {
            border-color: var(--primary);
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--gray-100);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .feature-card:hover .feature-icon {
            background: var(--primary);
            color: var(--secondary);
            transform: scale(1.1) rotate(-5deg);
        }

        .feature-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.8rem;
        }

        .feature-description {
            font-size: 0.95rem;
            color: var(--gray-500);
            line-height: 1.7;
        }

        /* ============ CARS SECTION ============ */
        .cars-section {
            background: var(--gray-50);
        }

        .section-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-header-left {
            text-align: left;
        }

        .section-header-left .section-badge {
            margin-bottom: 0.5rem;
        }

        .section-header-left .section-title {
            margin-bottom: 0;
        }

        .view-all-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            transition: var(--transition);
        }

        .view-all-link:hover {
            gap: 1rem;
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .car-card {
            background: var(--secondary);
            border-radius: 24px;
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
        }

        .car-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-2xl);
        }

        .car-image {
            position: relative;
            height: 220px;
            background: var(--gray-100);
            overflow: hidden;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            /* NO grayscale - Full color images */
        }

        .car-card:hover .car-image img {
            transform: scale(1.1);
        }

        .car-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--gray-300);
            background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
        }

        .car-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.4rem 1rem;
            background: var(--primary);
            color: var(--secondary);
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .car-favorite {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
            border: none;
        }

        .car-favorite:hover {
            background: var(--accent);
            color: var(--secondary);
        }

        .car-content {
            padding: 1.5rem;
        }

        .car-brand {
            font-size: 0.85rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.3rem;
        }

        .car-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .car-specs {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .car-spec {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: var(--gray-600);
            background: var(--gray-100);
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
        }

        .car-spec i {
            font-size: 0.8rem;
            color: var(--gray-400);
        }

        .car-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .car-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .car-price span {
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--gray-500);
        }

        .car-book-btn {
            padding: 0.8rem 1.5rem;
            background: var(--primary);
            color: var(--secondary);
            border: none;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .car-book-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .no-cars {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: var(--secondary);
            border-radius: 20px;
            border: 2px dashed var(--gray-300);
        }

        .no-cars i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        /* ============ HOW IT WORKS ============ */
        .how-section {
            background: var(--secondary);
        }

        .steps-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            position: relative;
        }

        .steps-container::before {
            content: '';
            position: absolute;
            top: 60px;
            left: 15%;
            right: 15%;
            height: 2px;
            background: var(--gray-200);
            z-index: 0;
        }

        .step-card {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 120px;
            height: 120px;
            background: var(--secondary);
            border: 3px solid var(--gray-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gray-300);
            transition: var(--transition);
            position: relative;
        }

        .step-card:hover .step-number {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--secondary);
            transform: scale(1.1);
        }

        .step-icon {
            position: absolute;
            bottom: -10px;
            right: -10px;
            width: 45px;
            height: 45px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
            font-size: 1rem;
            border: 4px solid var(--secondary);
        }

        .step-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.8rem;
        }

        .step-description {
            font-size: 0.95rem;
            color: var(--gray-500);
            line-height: 1.7;
            max-width: 250px;
            margin: 0 auto;
        }

        /* ============ STATS SECTION ============ */
        .stats-section {
            padding: 5rem 5%;
            background: var(--primary);
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3rem;
            position: relative;
            z-index: 1;
        }

        .stat-item {
            text-align: center;
            position: relative;
        }

        .stat-item::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 60%;
            background: rgba(255,255,255,0.1);
        }

        .stat-item:last-child::after {
            display: none;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: var(--secondary);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--secondary);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: rgba(255,255,255,0.7);
        }

        /* ============ TESTIMONIALS ============ */
        .testimonials-section {
            background: var(--gray-50);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .testimonial-card {
            background: var(--secondary);
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .testimonial-rating {
            display: flex;
            gap: 0.3rem;
            margin-bottom: 1.5rem;
        }

        .testimonial-rating i {
            color: #ffc107;
            font-size: 1rem;
        }

        .testimonial-text {
            font-size: 1.05rem;
            color: var(--gray-600);
            line-height: 1.8;
            margin-bottom: 2rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .testimonial-avatar {
            width: 55px;
            height: 55px;
            background: var(--gray-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .testimonial-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.2rem;
        }

        .testimonial-info span {
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        /* ============ CTA SECTION ============ */
        .cta-section {
            padding: 6rem 5%;
            background: var(--secondary);
        }

        .cta-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--primary);
            border-radius: 30px;
            padding: 4rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .cta-container::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-title {
            font-family: var(--font-display);
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .cta-description {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 2rem;
            line-height: 1.7;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-white {
            background: var(--secondary);
            color: var(--primary);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .btn-outline-white {
            background: transparent;
            color: var(--secondary);
            border: 2px solid rgba(255,255,255,0.3);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline-white:hover {
            background: var(--secondary);
            color: var(--primary);
        }

        .cta-image {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .cta-image img {
            max-width: 100%;
            height: auto;
            /* Full color image */
        }

        .cta-car-placeholder {
            font-size: 10rem;
            opacity: 0.3;
        }

        /* ============ BRANDS SECTION ============ */
        .brands-section {
            padding: 5rem 5%;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
        }

        .brands-title {
            text-align: center;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2rem;
        }

        .brands-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 4rem;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }

        .brand-item {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-300);
            transition: var(--transition);
            cursor: pointer;
        }

        .brand-item:hover {
            color: var(--primary);
            transform: scale(1.1);
        }

        /* ============ NEWSLETTER ============ */
        .newsletter-section {
            padding: 5rem 5%;
            background: var(--secondary);
        }

        .newsletter-container {
            max-width: 700px;
            margin: 0 auto;
            text-align: center;
        }

        .newsletter-title {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .newsletter-description {
            color: var(--gray-500);
            margin-bottom: 2rem;
        }

        .newsletter-form {
            display: flex;
            gap: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .newsletter-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 50px;
            font-size: 1rem;
            outline: none;
            transition: var(--transition);
            font-family: inherit;
        }

        .newsletter-input:focus {
            border-color: var(--primary);
        }

        .newsletter-btn {
            padding: 1rem 2rem;
            background: var(--primary);
            color: var(--secondary);
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .newsletter-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* ============ SCROLL TO TOP ============ */
        .scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: var(--secondary);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: var(--transition);
            z-index: 999;
            box-shadow: var(--shadow-lg);
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-to-top:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3.2rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .cars-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 3rem;
            }

            .steps-container::before {
                display: none;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .stat-item::after {
                display: none;
            }
        }

        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-text {
                max-width: 100%;
            }

            .hero-features {
                justify-content: center;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
            }

            .hero-image {
                display: none;
            }

            .search-form {
                grid-template-columns: repeat(2, 1fr);
            }

            .search-btn {
                grid-column: span 2;
                justify-content: center;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
                max-width: 600px;
                margin: 0 auto;
            }

            .cta-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .cta-buttons {
                justify-content: center;
            }

            .cta-image {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 7rem 5% 4rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-features {
                flex-direction: column;
                gap: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                width: 100%;
            }

            .hero-buttons .btn {
                width: 100%;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }

            .hero-stat {
                text-align: center;
            }

            .search-section {
                margin-top: -2rem;
            }

            .search-box {
                padding: 1.5rem;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .search-btn {
                grid-column: span 1;
            }

            .section {
                padding: 4rem 5%;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .cars-grid {
                grid-template-columns: 1fr;
            }

            .steps-container {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .cta-container {
                padding: 2.5rem 1.5rem;
            }

            .cta-title {
                font-size: 2rem;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .brands-grid {
                gap: 2rem;
            }

            .brand-item {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<!-- Preloader -->
<div class="preloader" id="preloader">
    <div class="preloader-logo">DriveEase</div>
    <div class="preloader-spinner"></div>
</div>

<!-- Include Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-bg-pattern"></div>
    <div class="hero-grid-lines"></div>
    
    <div class="hero-content">
        <div class="hero-text">
            <div class="hero-badge" data-aos="fade-down" data-aos-delay="100">
                <i class="fas fa-star"></i>
                #1 Rated Car Rental Service
            </div>
            
            <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                Find Your Perfect<br>
                <span>Ride Today</span>
            </h1>
            
            <p class="hero-description" data-aos="fade-up" data-aos-delay="300">
                Experience the freedom of the road with our premium fleet of vehicles. 
                From economy to luxury, we have the perfect car for every journey.
            </p>
            
            <div class="hero-features" data-aos="fade-up" data-aos-delay="400">
                <div class="hero-feature">
                    <i class="fas fa-check"></i>
                    <span>Free Cancellation</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-check"></i>
                    <span>24/7 Support</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-check"></i>
                    <span>Best Price</span>
                </div>
            </div>
            
            <div class="hero-buttons" data-aos="fade-up" data-aos-delay="500">
                <a href="<?php echo $base_url; ?>user/cars.php" class="btn btn-primary">
                    <i class="fas fa-car"></i> Browse Cars
                </a>
                <a href="#how-it-works" class="btn btn-outline">
                    <i class="fas fa-play-circle"></i> How It Works
                </a>
            </div>
            
            <div class="hero-stats" data-aos="fade-up" data-aos-delay="600">
                <div class="hero-stat">
                    <div class="hero-stat-number">
                        <span class="counter" data-count="<?php echo $totalCars ?: 50; ?>">0</span><span>+</span>
                    </div>
                    <div class="hero-stat-label">Cars Available</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number">
                        <span class="counter" data-count="<?php echo $totalUsers ?: 1000; ?>">0</span><span>+</span>
                    </div>
                    <div class="hero-stat-label">Happy Customers</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number">
                        <span class="counter" data-count="<?php echo $totalBookings ?: 500; ?>">0</span><span>+</span>
                    </div>
                    <div class="hero-stat-label">Trips Completed</div>
                </div>
            </div>
        </div>
        
        <div class="hero-image" data-aos="fade-left" data-aos-delay="400">
            <div class="hero-car-wrapper">
                <div class="hero-car-bg"></div>
                <div class="hero-car-image">
                    <!-- Add your hero car image here -->
                    <img src="<?php echo $base_url; ?>assets/images/car.jpg" alt="Premium Car" 
                        
                </div>
                
                <!-- <div class="hero-floating-card hero-floating-card-1">
                    <div class="floating-card-icon">🚗</div>
                    <div class="floating-card-text">
                        <h4>Premium Fleet</h4>
                        <p>Luxury vehicles</p>
                    </div>
                </div>
                
                <div class="hero-floating-card hero-floating-card-2">
                    <div class="floating-card-icon">⭐</div>
                    <div class="floating-card-text">
                        <h4>4.9 Rating</h4>
                        <p>5000+ reviews</p>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Box -->
<!-- <section class="search-section">
    <div class="search-box" data-aos="fade-up">
        <div class="search-box-header">
            <h3 class="search-box-title">Find Your Perfect Car</h3>
            <div class="search-tabs">
                <button class="search-tab active">All Cars</button>
                <button class="search-tab">Self Drive</button>
                <button class="search-tab">With Driver</button>
            </div>
        </div>
        
        <form class="search-form" action="<?php echo $base_url; ?>user/cars.php" method="GET">
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Pick-up Location</label>
                <select class="form-control" name="location">
                    <option value="">Select Location</option>
                    <option value="downtown">Downtown</option>
                    <option value="airport">Airport</option>
                    <option value="hotel">Hotel Delivery</option>
                </select>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-calendar"></i> Pick-up Date</label>
                <input type="date" class="form-control" name="pickup_date" 
                       min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-calendar"></i> Return Date</label>
                <input type="date" class="form-control" name="return_date"
                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-car"></i> Car Type</label>
                <select class="form-control" name="car_type">
                    <option value="">All Types</option>
                    <?php 
                    mysqli_data_seek($typesResult, 0);
                    while($type = mysqli_fetch_assoc($typesResult)): 
                    ?>
                        <option value="<?php echo htmlspecialchars($type['car_type']); ?>">
                            <?php echo htmlspecialchars($type['car_type']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</section> -->

<!-- Features Section -->
<section class="section features-section" id="features">
    <div class="section-container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge">Why Choose Us</span>
            <h2 class="section-title">Premium <span>Features</span></h2>
            <p class="section-description">
                We provide the best car rental experience with top-notch service
            </p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon">💰</div>
                <h3 class="feature-title">Best Price Guarantee</h3>
                <p class="feature-description">
                    We offer competitive prices with no hidden fees. Find a lower price? We'll match it!
                </p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon">🚗</div>
                <h3 class="feature-title">Wide Selection</h3>
                <p class="feature-description">
                    Choose from our extensive fleet of well-maintained vehicles to suit every need.
                </p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon">🛡️</div>
                <h3 class="feature-title">Full Insurance</h3>
                <p class="feature-description">
                    Drive with peace of mind knowing you're fully covered with our comprehensive insurance.
                </p>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon">📞</div>
                <h3 class="feature-title">24/7 Support</h3>
                <p class="feature-description">
                    Our dedicated support team is available round the clock to assist you anytime.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Cars Section -->
<section class="section cars-section" id="cars">
    <div class="section-container">
        <div class="section-header-flex" data-aos="fade-up">
            <div class="section-header-left">
                <span class="section-badge">Our Fleet</span>
                <h2 class="section-title">Featured <span>Cars</span></h2>
            </div>
            <a href="<?php echo $base_url; ?>user/cars.php" class="view-all-link">
                View All Cars <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="cars-grid">
            <?php if(mysqli_num_rows($featuredCarsResult) > 0): ?>
                <?php $delay = 100; ?>
                <?php while($car = mysqli_fetch_assoc($featuredCarsResult)): ?>
                    <div class="car-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="car-image">
                            <?php if(!empty($car['image']) && file_exists('uploads/car_images/' . $car['image'])): ?>
                                <img src="<?php echo $base_url; ?>uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($car['car_name']); ?>">
                            <?php else: ?>
                                <div class="car-image-placeholder">
                                    <i class="fas fa-car"></i>
                                </div>
                            <?php endif; ?>
                            <span class="car-badge"><?php echo htmlspecialchars($car['car_type']); ?></span>
                            <button class="car-favorite" type="button">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="car-content">
                            <div class="car-brand"><?php echo htmlspecialchars($car['brand']); ?></div>
                            <h3 class="car-name"><?php echo htmlspecialchars($car['car_name']); ?></h3>
                            <div class="car-specs">
                                <span class="car-spec">
                                    <i class="fas fa-gas-pump"></i>
                                    <?php echo htmlspecialchars($car['fuel_type']); ?>
                                </span>
                                <span class="car-spec">
                                    <i class="fas fa-cog"></i>
                                    <?php echo htmlspecialchars($car['transmission']); ?>
                                </span>
                                <span class="car-spec">
                                    <i class="fas fa-users"></i>
                                    <?php echo htmlspecialchars($car['seats']); ?> Seats
                                </span>
                            </div>
                            <div class="car-footer">
                                <div class="car-price">
                                    $<?php echo number_format($car['price_per_day'], 0); ?><span>/day</span>
                                </div>
                                <a href="<?php echo $base_url; ?>user/car_details.php?id=<?php echo $car['id']; ?>" 
                                   class="car-book-btn">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php $delay += 100; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-cars">
                    <i class="fas fa-car"></i>
                    <h3>No Cars Available</h3>
                    <p>Please check back later for available vehicles.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section how-section" id="how-it-works">
    <div class="section-container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge">Simple Process</span>
            <h2 class="section-title">How It <span>Works</span></h2>
            <p class="section-description">
                Rent a car in just 4 simple steps. Easy booking, seamless experience.
            </p>
        </div>
        
        <div class="steps-container">
            <div class="step-card" data-aos="fade-up" data-aos-delay="100">
                <div class="step-number">
                    1
                    <div class="step-icon"><i class="fas fa-search"></i></div>
                </div>
                <h3 class="step-title">Search & Choose</h3>
                <p class="step-description">
                    Browse our wide selection of vehicles and choose the perfect car for your needs.
                </p>
            </div>
            
            <div class="step-card" data-aos="fade-up" data-aos-delay="200">
                <div class="step-number">
                    2
                    <div class="step-icon"><i class="fas fa-calendar-check"></i></div>
                </div>
                <h3 class="step-title">Book Online</h3>
                <p class="step-description">
                    Select your dates, fill in details, and complete your booking in minutes.
                </p>
            </div>
            
            <div class="step-card" data-aos="fade-up" data-aos-delay="300">
                <div class="step-number">
                    3
                    <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                </div>
                <h3 class="step-title">Make Payment</h3>
                <p class="step-description">
                    Secure payment options available. Pay online or at pickup.
                </p>
            </div>
            
            <div class="step-card" data-aos="fade-up" data-aos-delay="400">
                <div class="step-number">
                    4
                    <div class="step-icon"><i class="fas fa-car"></i></div>
                </div>
                <h3 class="step-title">Drive Away</h3>
                <p class="step-description">
                    Pick up your car and enjoy your journey. Return when you're done!
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-icon"><i class="fas fa-car"></i></div>
            <div class="stat-number"><span class="counter" data-count="<?php echo $totalCars ?: 50; ?>">0</span>+</div>
            <div class="stat-label">Vehicles Available</div>
        </div>
        
        <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><span class="counter" data-count="<?php echo ($totalUsers ?: 100) * 10; ?>">0</span>+</div>
            <div class="stat-label">Happy Customers</div>
        </div>
        
        <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="stat-number"><span class="counter" data-count="25">0</span>+</div>
            <div class="stat-label">Pickup Locations</div>
        </div>
        
        <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
            <div class="stat-icon"><i class="fas fa-award"></i></div>
            <div class="stat-number"><span class="counter" data-count="10">0</span>+</div>
            <div class="stat-label">Years Experience</div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section testimonials-section" id="testimonials">
    <div class="section-container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge">Testimonials</span>
            <h2 class="section-title">What Our <span>Clients Say</span></h2>
            <p class="section-description">
                Don't just take our word for it. Here's what our customers say.
            </p>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Excellent service from start to finish! The car was in perfect condition and the booking process was seamless. Will definitely use again!"
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">JD</div>
                    <div class="testimonial-info">
                        <h4>John Doe</h4>
                        <span>Business Traveler</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Best car rental experience I've ever had. The prices are competitive and the customer support is amazing. Highly recommended!"
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">SW</div>
                    <div class="testimonial-info">
                        <h4>Sarah Williams</h4>
                        <span>Family Vacation</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "I was impressed by the variety of cars available. Got a great SUV for our road trip at an unbeatable price. Thank you DriveEase!"
                </p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">MC</div>
                    <div class="testimonial-info">
                        <h4>Mike Chen</h4>
                        <span>Adventure Seeker</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="cta-container" data-aos="fade-up">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Hit the Road?</h2>
            <p class="cta-description">
                Book your perfect car today and enjoy the freedom of the open road. 
                Special discounts available for first-time customers!
            </p>
            <div class="cta-buttons">
                <a href="<?php echo $base_url; ?>user/cars.php" class="btn-white">
                    <i class="fas fa-car"></i> Browse Cars
                </a>
               
            </div>
        </div>
        <div class="cta-image">
            <div class="cta-car-placeholder">🚗</div>
        </div>
    </div>
</section>

<!-- Brands Section -->
<section class="brands-section">
    <p class="brands-title">Trusted Car Brands</p>
    <div class="brands-grid">
        <div class="brand-item" data-aos="fade-up" data-aos-delay="100">Toyota</div>
        <div class="brand-item" data-aos="fade-up" data-aos-delay="150">BMW</div>
        <div class="brand-item" data-aos="fade-up" data-aos-delay="200">Mercedes</div>
        <div class="brand-item" data-aos="fade-up" data-aos-delay="250">Audi</div>
        <div class="brand-item" data-aos="fade-up" data-aos-delay="300">Honda</div>
        <div class="brand-item" data-aos="fade-up" data-aos-delay="350">Ford</div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section" id="contact">
    <div class="newsletter-container" data-aos="fade-up">
        <h2 class="newsletter-title">Get Exclusive Deals</h2>
        <p class="newsletter-description">Subscribe to our newsletter and receive special offers and updates.</p>
        <form class="newsletter-form" id="newsletterForm">
            <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
            <button type="submit" class="newsletter-btn">Subscribe</button>
        </form>
    </div>
</section>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollToTop">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Main JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============ PRELOADER ============
    window.addEventListener('load', function() {
        setTimeout(() => {
            document.getElementById('preloader').classList.add('hidden');
        }, 1000);
    });

    // ============ INITIALIZE AOS ============
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 50
    });

    // ============ COUNTER ANIMATION ============
    const counters = document.querySelectorAll('.counter');
    let countersAnimated = false;

    function animateCounters() {
        if (countersAnimated) return;
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-count'));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            };

            updateCounter();
        });
        
        countersAnimated = true;
    }

    // Trigger counter animation when visible
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('.hero-stats, .stats-section').forEach(section => {
        statsObserver.observe(section);
    });

    // ============ SCROLL TO TOP ============
    const scrollToTopBtn = document.getElementById('scrollToTop');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 500) {
            scrollToTopBtn.classList.add('show');
        } else {
            scrollToTopBtn.classList.remove('show');
        }
    });

    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // ============ SEARCH TABS ============
    document.querySelectorAll('.search-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ============ NEWSLETTER FORM ============
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('.newsletter-btn');
            const input = this.querySelector('.newsletter-input');
            const originalText = btn.textContent;
            
            btn.textContent = 'Subscribing...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.textContent = '✓ Subscribed!';
                btn.style.background = '#22c55e';
                input.value = '';
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '';
                    btn.disabled = false;
                }, 2000);
            }, 1500);
        });
    }

    // ============ FAVORITE BUTTON ============
    document.querySelectorAll('.car-favorite').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const icon = this.querySelector('i');
            
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                this.style.background = '#ff4444';
                this.style.color = 'white';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                this.style.background = '';
                this.style.color = '';
            }
        });
    });

    // ============ SMOOTH SCROLL ============
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 100;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ============ DATE VALIDATION ============
    const pickupDate = document.querySelector('input[name="pickup_date"]');
    const returnDate = document.querySelector('input[name="return_date"]');
    
    if (pickupDate && returnDate) {
        pickupDate.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            selectedDate.setDate(selectedDate.getDate() + 1);
            returnDate.min = selectedDate.toISOString().split('T')[0];
            
            if (returnDate.value && new Date(returnDate.value) <= new Date(this.value)) {
                returnDate.value = selectedDate.toISOString().split('T')[0];
            }
        });
    }
});
</script>

</body>
</html>