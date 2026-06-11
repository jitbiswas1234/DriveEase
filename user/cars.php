<?php
session_start();
require_once '../config/database.php';
require_once '../config/base_url.php';

// Fetch cars from database
$query = "SELECT * FROM cars ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<?php include("../includes/navbar.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars | DriveEase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #000000;
            --accent: #ff4444;
            --accent-hover: #cc0000;
            --bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-main: #1a1a1a;
            --text-muted: #6b7280;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        /* Modern Navbar */
      

        .logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .logo span { color: var(--accent); }

        

        /* Professional Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
            padding: 100px 5% 140px;
            text-align: center;
            color: white;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -2px;
        }

        .hero-section p {
            font-size: 1.1rem;
            color: #aaa;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Floating Search/Filter Bar */
        .search-container {
            max-width: 1000px;
            margin: -50px auto 60px;
            background: white;
            padding: 25px;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr auto;
            gap: 20px;
            align-items: center;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            padding: 0 15px;
            border-right: 1px solid #eee;
        }

        .input-group:last-of-type { border-right: none; }

        .input-group label {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .input-group input, .input-group select {
            border: none;
            outline: none;
            font-weight: 600;
            font-size: 0.95rem;
            background: transparent;
        }

        /* Car Grid Layout */
        .container { max-width: 1300px; margin: 0 auto; padding: 0 20px; }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        /* Enhanced Car Card */
        .card {
            background: var(--card-bg);
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.04);
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 60px -15px rgba(0,0,0,0.12);
        }

        .image-wrapper {
            position: relative;
            padding: 12px;
        }

        .car-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 20px;
        }

        .status-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            z-index: 5;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .status-available { background: #00ff88; color: #004d2a; }
        .status-booked { background: #ff4444; color: white; }

        .card-body { padding: 5px 25px 25px; }

        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--primary);
        }

        .card-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: block;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            padding: 15px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 20px;
        }

        .feature-item {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .feature-item i { color: var(--primary); font-size: 0.9rem; }

        .price-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-val {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
        }

        .price-val span { font-size: 0.85rem; color: var(--text-muted); font-weight: 400; }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--accent); transform: scale(1.05); }

        .btn-outline {
            background: transparent;
            border: 1.5px solid #eee;
            color: var(--text-main);
        }

        .btn-outline:hover { border-color: var(--primary); background: #f9f9f9; }

        /* Footer */
        
    </style>
</head>

<body>

   

    <header class="hero-section">
        <h1>Experience Premium.</h1>
        <p>Unlock the journey of a lifetime with our curated collection of high-performance and luxury vehicles.</p>
    </header>

    <div class="container">
        <div class="search-container">
            <div class="input-group">
                <label>Find Your Car</label>
                <input type="text" id="searchInput" placeholder="Search brand or model...">
            </div>
            <div class="input-group">
                <label>Car Category</label>
                <select id="categoryFilter">
                    <option value="">All Types</option>
                    <option value="sedan">Sedan</option>
                    <option value="suv">SUV</option>
                    <option value="hatchback">Hatchback</option>
                    <option value="coupe">Coupe</option>
                </select>
            </div>
            <div class="input-group">
                <label>Pricing</label>
                <select id="priceFilter">
                    <option value="">Any Price</option>
                    <option value="3000">Under ₹3000</option>
                    <option value="7000">₹3000 - ₹7000</option>
                    <option value="10000">₹7000+</option>
                </select>
            </div>
            <button class="btn btn-primary" type="button" id="searchBtn" style="padding: 15px 30px;">
                <i class="fa fa-search"></i> Find
            </button>
        </div>

        <div class="car-grid">
            <?php while($car = mysqli_fetch_assoc($result)): ?>
                <div class="card" data-brand="<?php echo strtolower($car['brand']); ?>" data-model="<?php echo strtolower($car['car_name']); ?>" data-type="<?php echo strtolower($car['car_type']); ?>" data-price="<?php echo $car['price_per_day']; ?>">
                    <?php if($car['status'] == "Available"): ?>
                        <span class="status-badge status-available">Available</span>
                    <?php else: ?>
                        <span class="status-badge status-booked">Reserved</span>
                    <?php endif; ?>

                    <div class="image-wrapper">
                        <img src="../uploads/car_images/<?php echo $car['image']; ?>" class="car-image" alt="Car Image">
                    </div>

                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($car['car_name']); ?></h3>
                        <span class="card-subtitle"><?php echo htmlspecialchars($car['brand']); ?> • <?php echo $car['year']; ?></span>

                        <div class="features-grid">
                            <div class="feature-item">
                                <i class="fa fa-gas-pump"></i>
                                <span><?php echo $car['fuel_type']; ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fa fa-cog"></i>
                                <span><?php echo $car['transmission']; ?></span>
                            </div>
                            <div class="feature-item">
                                <i class="fa fa-user"></i>
                                <span><?php echo $car['seats']; ?> Seats</span>
                            </div>
                        </div>

                        <div class="price-container">
                            <div class="price-val">₹<?php echo number_format($car['price_per_day']); ?><span>/day</span></div>
                            <div style="display:flex; gap: 8px;">
                              <a href="../user/car_details.php?id=<?php echo $car['id']; ?>" 
class="btn btn-outline">

<i class="fa-solid fa-circle-info"></i>

</a>
                                <?php if($car['status'] == "Available"): ?>
                                    <a href="../booking/book_car.php?id=<?php echo $car['id']; ?>" class="btn btn-primary">
                                        Book
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

   

    <script>
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const priceFilter = document.getElementById('priceFilter');
        const searchBtn = document.getElementById('searchBtn');
        const cards = document.querySelectorAll('.card');

        function filterCars() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const categoryValue = categoryFilter.value.toLowerCase();
            const priceValue = priceFilter.value;

            cards.forEach(card => {
                const brand = card.getAttribute('data-brand');
                const model = card.getAttribute('data-model');
                const type = card.getAttribute('data-type');
                const price = parseInt(card.getAttribute('data-price'));

                let matchesSearch = true;
                let matchesCategory = true;
                let matchesPrice = true;

                // Search filter
                if (searchTerm) {
                    matchesSearch = brand.includes(searchTerm) || model.includes(searchTerm);
                }

                // Category filter
                if (categoryValue) {
                    matchesCategory = type === categoryValue;
                }

                // Price filter
                if (priceValue) {
                    const maxPrice = parseInt(priceValue);
                    if (priceValue === '10000') {
                        matchesPrice = price >= 7000;
                    } else {
                        matchesPrice = price <= maxPrice;
                    }
                }

                // Show/hide card
                if (matchesSearch && matchesCategory && matchesPrice) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Event listeners
        searchBtn.addEventListener('click', filterCars);
        searchInput.addEventListener('keyup', filterCars);
        categoryFilter.addEventListener('change', filterCars);
        priceFilter.addEventListener('change', filterCars);
    </script>
<?php include("../includes/footer.php"); ?>
</body>
</html>