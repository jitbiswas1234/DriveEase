<?php
// includes/footer.php
// Get current year for copyright
$currentYear = date('Y');

// Company/Website information
$companyName = "DriveEase Car Rental";
$companyEmail = "info@driveease.com";
$companyPhone = "+1 (555) 123-4567";
$companyAddress = "123 Auto Drive, Motor City, MC 12345";

// Social media links
$socialMedia = [
    'facebook' => 'https://facebook.com/driveease',
    'twitter' => 'https://twitter.com/driveease',
    'instagram' => 'https://instagram.com/driveease',
    'linkedin' => 'https://linkedin.com/company/driveease',
    'youtube' => 'https://youtube.com/driveease'
];

// Get base URL
require_once __DIR__ . '/../config/base_url.php';
?>

<!-- Footer Styles -->
<style>
    /* ============ FOOTER BASE ============ */
    .footer {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        color: #e4e4e4;
        position: relative;
        overflow: hidden;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #e94560, #f39c12, #e94560);
        background-size: 200% 100%;
        animation: gradientMove 3s ease-in-out infinite;
    }

    @keyframes gradientMove {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    /* Animated Background Elements */
    .footer-bg-elements {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        overflow: hidden;
    }

    .footer-car-silhouette {
        position: absolute;
        bottom: 20px;
        right: -100px;
        width: 200px;
        height: 60px;
        opacity: 0.03;
        animation: carDrive 20s linear infinite;
    }

    @keyframes carDrive {
        0% { transform: translateX(0); }
        100% { transform: translateX(calc(-100vw - 200px)); }
    }

    .footer-road {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, rgba(233, 69, 96, 0.3), transparent);
        animation: roadGlow 2s ease-in-out infinite;
    }

    @keyframes roadGlow {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.6; }
    }

    .footer-particle {
        position: absolute;
        width: 4px;
        height: 4px;
        background: rgba(233, 69, 96, 0.4);
        border-radius: 50%;
        animation: floatParticle 15s infinite;
    }

    @keyframes floatParticle {
        0%, 100% {
            transform: translateY(100%) rotate(0deg);
            opacity: 0;
        }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% {
            transform: translateY(-100vh) rotate(720deg);
            opacity: 0;
        }
    }

    /* ============ FOOTER MAIN ============ */
    .footer-main {
        padding: 5rem 2rem 3rem;
        position: relative;
        z-index: 1;
    }

    .footer-container {
        max-width: 1300px;
        margin: 0 auto;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
        gap: 3rem;
    }

    /* ============ FOOTER BRAND ============ */
    .footer-brand {
        padding-right: 2rem;
    }

    .footer-logo {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 1.5rem;
        text-decoration: none;
        transition: transform 0.3s ease;
    }

    .footer-logo:hover {
        transform: scale(1.02);
    }

    .footer-logo-icon {
        width: 55px;
        height: 55px;
        background: linear-gradient(135deg, #e94560 0%, #f39c12 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        animation: logoFloat 3s ease-in-out infinite;
        box-shadow: 0 10px 30px rgba(233, 69, 96, 0.3);
    }

    @keyframes logoFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    .footer-logo-text {
        font-size: 1.6rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.2;
    }

    .footer-logo-text span {
        display: block;
        font-size: 0.75rem;
        font-weight: 400;
        color: #e94560;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .footer-brand p {
        color: #a0a0a0;
        line-height: 1.8;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    /* Footer Stats */
    .footer-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .footer-stat {
        text-align: center;
        position: relative;
    }

    .footer-stat:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 40px;
        width: 1px;
        background: rgba(255, 255, 255, 0.1);
    }

    .footer-stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #e94560;
        display: block;
        line-height: 1;
        margin-bottom: 0.3rem;
    }

    .footer-stat-label {
        font-size: 0.75rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* ============ FOOTER SECTIONS ============ */
    .footer-section h4 {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.8rem;
    }

    .footer-section h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background: linear-gradient(90deg, #e94560, #f39c12);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .footer-section:hover h4::after {
        width: 60px;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 0.8rem;
    }

    .footer-links a {
        color: #a0a0a0;
        text-decoration: none;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        position: relative;
        padding-left: 0;
    }

    .footer-links a::before {
        content: '';
        width: 0;
        height: 2px;
        background: #e94560;
        position: absolute;
        bottom: -2px;
        left: 0;
        transition: width 0.3s ease;
    }

    .footer-links a:hover {
        color: #e94560;
        padding-left: 10px;
    }

    .footer-links a:hover::before {
        width: 100%;
    }

    .footer-links a i {
        font-size: 0.8rem;
        color: #e94560;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
    }

    .footer-links a:hover i {
        opacity: 1;
        transform: translateX(0);
    }

    /* ============ CONTACT INFO ============ */
    .footer-contact-list {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .footer-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.03);
        transition: all 0.3s ease;
    }

    .footer-contact-item:hover {
        background: rgba(233, 69, 96, 0.05);
        border-color: rgba(233, 69, 96, 0.1);
        transform: translateX(5px);
    }

    .footer-contact-icon {
        width: 45px;
        height: 45px;
        min-width: 45px;
        background: linear-gradient(135deg, rgba(233, 69, 96, 0.2), rgba(243, 156, 18, 0.1));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }

    .footer-contact-item:hover .footer-contact-icon {
        background: linear-gradient(135deg, #e94560, #f39c12);
        transform: scale(1.1) rotate(5deg);
    }

    .footer-contact-text h5 {
        color: #fff;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }

    .footer-contact-text p,
    .footer-contact-text a {
        color: #888;
        font-size: 0.9rem;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-contact-text a:hover {
        color: #e94560;
    }

    /* ============ NEWSLETTER ============ */
    .footer-newsletter {
        padding-left: 1rem;
    }

    .footer-newsletter p {
        color: #888;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        line-height: 1.7;
    }

    .newsletter-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .newsletter-input-wrapper {
        position: relative;
    }

    .newsletter-input {
        width: 100%;
        padding: 1rem 1.2rem;
        padding-right: 50px;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        color: #fff;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        outline: none;
    }

    .newsletter-input::placeholder {
        color: #666;
    }

    .newsletter-input:focus {
        border-color: #e94560;
        background: rgba(233, 69, 96, 0.05);
        box-shadow: 0 0 20px rgba(233, 69, 96, 0.1);
    }

    .newsletter-input-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        transition: color 0.3s ease;
    }

    .newsletter-input:focus + .newsletter-input-icon {
        color: #e94560;
    }

    .newsletter-btn {
        width: 100%;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #e94560 0%, #f39c12 100%);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .newsletter-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }

    .newsletter-btn:hover::before {
        left: 100%;
    }

    .newsletter-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(233, 69, 96, 0.4);
    }

    .newsletter-btn i {
        transition: transform 0.3s ease;
    }

    .newsletter-btn:hover i {
        transform: translateX(5px);
    }

    /* Social Links */
    .footer-social {
        margin-top: 2rem;
    }

    .footer-social h5 {
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .social-links {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .social-link {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a0a0a0;
        font-size: 1.2rem;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .social-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #e94560, #f39c12);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .social-link:hover {
        transform: translateY(-5px) rotate(5deg);
        box-shadow: 0 10px 25px rgba(233, 69, 96, 0.3);
        border-color: transparent;
    }

    .social-link:hover::before {
        opacity: 1;
    }

    .social-link i {
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease;
    }

    .social-link:hover i {
        color: #fff;
        transform: scale(1.2);
    }

    /* ============ FOOTER BOTTOM ============ */
    .footer-bottom {
        background: rgba(0, 0, 0, 0.2);
        padding: 1.5rem 2rem;
        position: relative;
        z-index: 1;
    }

    .footer-bottom-container {
        max-width: 1300px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-copyright {
        color: #888;
        font-size: 0.9rem;
    }

    .footer-copyright a {
        color: #e94560;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .footer-copyright a:hover {
        color: #f39c12;
    }

    .footer-legal-links {
        display: flex;
        gap: 2rem;
    }

    .footer-legal-links a {
        color: #888;
        text-decoration: none;
        font-size: 0.85rem;
        transition: color 0.3s ease;
        position: relative;
    }

    .footer-legal-links a::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 0;
        height: 1px;
        background: #e94560;
        transition: width 0.3s ease;
    }

    .footer-legal-links a:hover {
        color: #e94560;
    }

    .footer-legal-links a:hover::after {
        width: 100%;
    }

    .footer-payment {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .footer-payment span {
        color: #666;
        font-size: 0.85rem;
    }

    .payment-icons {
        display: flex;
        gap: 0.5rem;
    }

    .payment-icon {
        width: 45px;
        height: 28px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        color: #888;
        transition: all 0.3s ease;
    }

    .payment-icon:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    /* ============ SCROLL TO TOP ============ */
    .scroll-to-top {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 55px;
        height: 55px;
        background: linear-gradient(135deg, #e94560 0%, #f39c12 100%);
        border: none;
        border-radius: 15px;
        color: #fff;
        font-size: 1.3rem;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.4s ease;
        z-index: 999;
        box-shadow: 0 10px 30px rgba(233, 69, 96, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .scroll-to-top.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .scroll-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(233, 69, 96, 0.5);
    }

    .scroll-to-top i {
        animation: bounceUp 2s infinite;
    }

    @keyframes bounceUp {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-5px); }
        60% { transform: translateY(-3px); }
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 1024px) {
        .footer-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 2.5rem;
        }

        .footer-brand {
            grid-column: span 2;
        }

        .footer-stats {
            max-width: 400px;
        }
    }

    @media (max-width: 768px) {
        .footer-main {
            padding: 3rem 1.5rem 2rem;
        }

        .footer-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .footer-brand {
            grid-column: span 1;
            padding-right: 0;
        }

        .footer-newsletter {
            padding-left: 0;
        }

        .footer-stats {
            max-width: 100%;
        }

        .footer-bottom-container {
            flex-direction: column;
            text-align: center;
        }

        .footer-legal-links {
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
        }

        .footer-payment {
            flex-direction: column;
        }

        .scroll-to-top {
            bottom: 1.5rem;
            right: 1.5rem;
            width: 50px;
            height: 50px;
        }
    }

    @media (max-width: 480px) {
        .footer-stats {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .footer-stat:not(:last-child)::after {
            display: none;
        }

        .footer-stat {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .footer-stat:last-child {
            border-bottom: none;
        }
    }

    /* ============ ANIMATION CLASSES ============ */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .fade-in-up.animated {
        opacity: 1;
        transform: translateY(0);
    }

    .fade-in-left {
        opacity: 0;
        transform: translateX(-30px);
        transition: all 0.6s ease;
    }

    .fade-in-left.animated {
        opacity: 1;
        transform: translateX(0);
    }

    .fade-in-right {
        opacity: 0;
        transform: translateX(30px);
        transition: all 0.6s ease;
    }

    .fade-in-right.animated {
        opacity: 1;
        transform: translateX(0);
    }
</style>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Footer HTML -->
<footer class="footer">
    <!-- Background Elements -->
    <div class="footer-bg-elements">
        <div class="footer-road"></div>
        <svg class="footer-car-silhouette" viewBox="0 0 200 60" fill="rgba(255,255,255,0.5)">
            <path d="M20,45 L30,45 L35,35 L55,30 L75,30 L90,35 L95,45 L105,45 L110,40 L150,40 L160,35 L175,35 L180,45 L190,45 L190,50 L170,50 L165,55 L145,55 L140,50 L60,50 L55,55 L35,55 L30,50 L10,50 L10,45 Z"/>
        </svg>
        <?php for($i = 0; $i < 10; $i++): ?>
            <div class="footer-particle" style="left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo rand(0, 15); ?>s; animation-duration: <?php echo rand(10, 20); ?>s;"></div>
        <?php endfor; ?>
    </div>

    <!-- Main Footer Content -->
    <div class="footer-main">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Brand Section -->
                <div class="footer-brand fade-in-up">
                    <a href="<?php echo $base_url; ?>" class="footer-logo">
                        <div class="footer-logo-icon">🚗</div>
                        <div class="footer-logo-text">
                            DriveEase
                            <span>Car Rental</span>
                        </div>
                    </a>
                    <p>
                        Experience the freedom of the road with our premium car rental services. 
                        We offer a wide selection of vehicles to meet all your travel needs, 
                        from economy cars to luxury vehicles.
                    </p>
                    <div class="footer-stats">
                        <div class="footer-stat">
                            <span class="footer-stat-number" data-count="500">0</span>
                            <span class="footer-stat-label">Cars</span>
                        </div>
                        <div class="footer-stat">
                            <span class="footer-stat-number" data-count="10000">0</span>
                            <span class="footer-stat-label">Customers</span>
                        </div>
                        <div class="footer-stat">
                            <span class="footer-stat-number" data-count="25">0</span>
                            <span class="footer-stat-label">Locations</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section fade-in-up" style="transition-delay: 0.1s;">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="<?php echo $base_url; ?>">
                                <i class="fas fa-chevron-right"></i> Home
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/cars.php">
                                <i class="fas fa-chevron-right"></i> Browse Cars
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/my_bookings.php">
                                <i class="fas fa-chevron-right"></i> My Bookings
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/profile.php">
                                <i class="fas fa-chevron-right"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>about.php">
                                <i class="fas fa-chevron-right"></i> About Us
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>contact.php">
                                <i class="fas fa-chevron-right"></i> Contact Us
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Car Categories -->
                <div class="footer-section fade-in-up" style="transition-delay: 0.2s;">
                    <h4>Car Categories</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="<?php echo $base_url; ?>user/cars.php?type=economy">
                                <i class="fas fa-chevron-right"></i> Economy Cars
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/cars.php?type=compact">
                                <i class="fas fa-chevron-right"></i> Compact Cars
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/cars.php?type=suv">
                                <i class="fas fa-chevron-right"></i> SUVs
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/cars.php?type=luxury">
                                <i class="fas fa-chevron-right"></i> Luxury Cars
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/cars.php?type=sports">
                                <i class="fas fa-chevron-right"></i> Sports Cars
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>user/cars.php?type=van">
                                <i class="fas fa-chevron-right"></i> Vans & Minivans
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact & Newsletter -->
                <div class="footer-newsletter fade-in-up" style="transition-delay: 0.3s;">
                    <div class="footer-section">
                        <h4>Contact Us</h4>
                        <div class="footer-contact-list">
                            <div class="footer-contact-item">
                                <div class="footer-contact-icon">📍</div>
                                <div class="footer-contact-text">
                                    <h5>Address</h5>
                                    <p><?php echo htmlspecialchars($companyAddress); ?></p>
                                </div>
                            </div>
                            <div class="footer-contact-item">
                                <div class="footer-contact-icon">📞</div>
                                <div class="footer-contact-text">
                                    <h5>Phone</h5>
                                    <a href="tel:<?php echo htmlspecialchars($companyPhone); ?>">
                                        <?php echo htmlspecialchars($companyPhone); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="footer-contact-item">
                                <div class="footer-contact-icon">✉️</div>
                                <div class="footer-contact-text">
                                    <h5>Email</h5>
                                    <a href="mailto:<?php echo htmlspecialchars($companyEmail); ?>">
                                        <?php echo htmlspecialchars($companyEmail); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Newsletter Form -->
                    <div class="footer-section" style="margin-top: 1.5rem;">
                        <h4>Newsletter</h4>
                        <p>Subscribe for exclusive deals and updates!</p>
                        <form class="newsletter-form" id="newsletterForm">
                            <div class="newsletter-input-wrapper">
                                <input 
                                    type="email" 
                                    class="newsletter-input" 
                                    placeholder="Enter your email"
                                    required
                                >
                                <i class="fas fa-envelope newsletter-input-icon"></i>
                            </div>
                            <button type="submit" class="newsletter-btn">
                                Subscribe <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Social Links -->
                    <div class="footer-social">
                        <h5>Follow Us</h5>
                        <div class="social-links">
                            <?php if (!empty($socialMedia['facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($socialMedia['facebook']); ?>" 
                                   class="social-link" 
                                   target="_blank" 
                                   title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($socialMedia['twitter'])): ?>
                                <a href="<?php echo htmlspecialchars($socialMedia['twitter']); ?>" 
                                   class="social-link" 
                                   target="_blank" 
                                   title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($socialMedia['instagram'])): ?>
                                <a href="<?php echo htmlspecialchars($socialMedia['instagram']); ?>" 
                                   class="social-link" 
                                   target="_blank" 
                                   title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($socialMedia['linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($socialMedia['linkedin']); ?>" 
                                   class="social-link" 
                                   target="_blank" 
                                   title="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($socialMedia['youtube'])): ?>
                                <a href="<?php echo htmlspecialchars($socialMedia['youtube']); ?>" 
                                   class="social-link" 
                                   target="_blank" 
                                   title="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <div class="footer-copyright">
                &copy; <?php echo $currentYear; ?> 
                <a href="<?php echo $base_url; ?>"><strong><?php echo htmlspecialchars($companyName); ?></strong></a>. 
                All rights reserved.
            </div>
            
            <div class="footer-legal-links">
                <a href="<?php echo $base_url; ?>privacy.php">Privacy Policy</a>
                <a href="<?php echo $base_url; ?>terms.php">Terms of Service</a>
                <a href="<?php echo $base_url; ?>faq.php">FAQ</a>
            </div>
            
            <div class="footer-payment">
                <span>We Accept:</span>
                <div class="payment-icons">
                    <div class="payment-icon"><i class="fab fa-cc-visa"></i></div>
                    <div class="payment-icon"><i class="fab fa-cc-mastercard"></i></div>
                    <div class="payment-icon"><i class="fab fa-cc-paypal"></i></div>
                    <div class="payment-icon"><i class="fab fa-cc-stripe"></i></div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Footer Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============ SCROLL TO TOP ============
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
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

    // ============ FOOTER ANIMATION ON SCROLL ============
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                
                // Animate counters when footer is visible
                if (entry.target.querySelector('.footer-stat-number')) {
                    animateCounters();
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right').forEach(el => {
        observer.observe(el);
    });

    // ============ COUNTER ANIMATION ============
    let countersAnimated = false;
    
    function animateCounters() {
        if (countersAnimated) return;
        countersAnimated = true;

        document.querySelectorAll('.footer-stat-number[data-count]').forEach(counter => {
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
    }

    // ============ NEWSLETTER FORM ============
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('.newsletter-btn');
            const input = this.querySelector('.newsletter-input');
            const originalText = btn.innerHTML;
            
            // Loading state
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            btn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Success state
                btn.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
                btn.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
                input.value = '';
                
                // Reset after 3 seconds
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                    btn.disabled = false;
                }, 3000);
            }, 1500);
        });
    }

    // ============ SMOOTH HOVER EFFECTS ============
    document.querySelectorAll('.footer-contact-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.querySelector('.footer-contact-icon').style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.querySelector('.footer-contact-icon').style.transform = '';
        });
    });

    // ============ PARALLAX EFFECT ON PARTICLES ============
    let ticking = false;
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                const scrolled = window.pageYOffset;
                const footer = document.querySelector('.footer');
                
                if (footer) {
                    const footerTop = footer.offsetTop;
                    const footerVisible = scrolled + window.innerHeight > footerTop;
                    
                    if (footerVisible) {
                        document.querySelectorAll('.footer-particle').forEach((particle, index) => {
                            const speed = (index % 3 + 1) * 0.02;
                            particle.style.transform = `translateY(${(scrolled - footerTop) * speed}px)`;
                        });
                    }
                }
                
                ticking = false;
            });
            
            ticking = true;
        }
    });
});
</script>