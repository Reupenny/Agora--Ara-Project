<?php
/*
	Insert Sample Data Script
	Creates sample users, businesses, and products for testing
*/

// Load configuration
include 'lib/database.php';
include 'lib/interfaces.php';

$config = parse_ini_file('config/website.conf');
$db = new Database(
	$config['dbHost'],
	$config['dbUser'],
	$config['dbPassword'],
	$config['dbDatabase']
);

echo "<!DOCTYPE html>
<html>
<head>
	<title>Insert Sample Data - Agora</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			max-width: 800px;
			margin: 50px auto;
			padding: 20px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
		}
		.container {
			background: white;
			padding: 40px;
			border-radius: 10px;
			box-shadow: 0 10px 40px rgba(0,0,0,0.2);
		}
		h1 {
			color: #667eea;
			margin-top: 0;
		}
		.success {
			color: #10b981;
			padding: 10px;
			margin: 10px 0;
			background: #d1fae5;
			border-radius: 5px;
		}
		.error {
			color: #ef4444;
			padding: 10px;
			margin: 10px 0;
			background: #fee2e2;
			border-radius: 5px;
		}
		.info {
			color: #3b82f6;
			padding: 10px;
			margin: 10px 0;
			background: #dbeafe;
			border-radius: 5px;
		}
		.btn {
			display: inline-block;
			padding: 12px 24px;
			background: #667eea;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			margin-top: 20px;
		}
		.btn:hover {
			background: #5568d3;
		}
	</style>
</head>
<body>
	<div class='container'>
		<h1>🌱 Insert Sample Data</h1>";

try {
	// 1. Insert sample users
	echo "<h2>Creating Users...</h2>";
	
	$users = [
		['john.seller', 'john@greenhouse.co.nz', 'John', 'Smith', 'Seller'],
		['jane.buyer', 'jane@example.com', 'Jane', 'Doe', 'Buyer'],
		['admin', 'admin@agora.co.nz', 'Admin', 'User', 'Agora Admin']
	];
	
	foreach ($users as $user) {
		$salt = bin2hex(random_bytes(16));
		$password = 'password123';
		$hash = hash('sha256', $password . $salt);
		
		$sql = "INSERT INTO users (username, email, first_name, last_name, password_hash, salt, account_type) 
		        VALUES (?, ?, ?, ?, ?, ?, ?)
		        ON DUPLICATE KEY UPDATE username=username";
		
		$db->executePrepared($sql, array_merge($user, [$hash, $salt]));
		echo "<div class='success'>✓ Created user: {$user[0]} (password: $password)</div>";
	}
	
	// 2. Insert sample businesses
	echo "<h2>Creating Businesses...</h2>";
	
	$businesses = [
		['Green House Botanicals', 'Auckland, New Zealand', 'Premier nursery specializing in rare tropical plants. Family-owned for over 15 years.', 'True'],
		['Tropical Plants Co.', 'Wellington, New Zealand', 'Exotic plant specialists bringing the tropics to your home.', 'True'],
		['Urban Garden Supply', 'Christchurch, New Zealand', 'Everything you need for urban gardening and indoor plants.', 'True']
	];
	
	foreach ($businesses as $business) {
		$sql = "INSERT INTO businesses (business_name, business_location, details, is_active) 
		        VALUES (?, ?, ?, ?)";
		$db->executePrepared($sql, $business);
		echo "<div class='success'>✓ Created business: {$business[0]}</div>";
	}
	
	// Get business IDs
	$result = $db->query("SELECT business_id, business_name FROM businesses ORDER BY business_id");
	$businessMap = [];
	foreach ($result as $row) {
		$businessMap[$row['business_name']] = $row['business_id'];
	}
	
	// 3. Link users to businesses
	echo "<h2>Creating Business Associations...</h2>";
	
	$associations = [
		['john.seller', $businessMap['Green House Botanicals'], 'Administrator'],
		['john.seller', $businessMap['Tropical Plants Co.'], 'Seller']
	];
	
	foreach ($associations as $assoc) {
		$sql = "INSERT INTO business_association (username, business_id, role_name, is_active) 
		        VALUES (?, ?, ?, 'True')
		        ON DUPLICATE KEY UPDATE username=username";
		$db->executePrepared($sql, $assoc);
		echo "<div class='success'>✓ Linked user {$assoc[0]} to business {$assoc[1]} as {$assoc[2]}</div>";
	}
	
	// 4. Insert sample products
	echo "<h2>Creating Products...</h2>";
	
	$products = [
		['Variegated Monstera Deliciosa', $businessMap['Green House Botanicals'], 
		 'A stunning Variegated Monstera Deliciosa with beautiful white and cream variegation. Perfect for collectors!', 
		 149.99, 5],
		['Monstera Adansonii', $businessMap['Green House Botanicals'], 
		 'Swiss Cheese Vine with beautiful fenestrated leaves. Easy to care for and fast-growing.', 
		 45.99, 12],
		['Philodendron Pink Princess', $businessMap['Green House Botanicals'], 
		 'Highly sought-after pink variegated philodendron. Each plant has unique pink splashes.', 
		 89.99, 3],
		['Alocasia Polly', $businessMap['Green House Botanicals'], 
		 'African Mask Plant with striking dark green leaves and white veins. Tropical beauty!', 
		 39.99, 8],
		['Philodendron Birkin', $businessMap['Tropical Plants Co.'], 
		 'White-striped philodendron with compact growth. Perfect for small spaces.', 
		 65.99, 10],
		['String of Hearts', $businessMap['Tropical Plants Co.'], 
		 'Trailing succulent with heart-shaped leaves. Perfect for hanging baskets.', 
		 29.99, 15],
		['Fiddle Leaf Fig', $businessMap['Urban Garden Supply'], 
		 'Popular statement plant with large, violin-shaped leaves. Thrives in bright light.', 
		 79.99, 6]
	];
	
	foreach ($products as $product) {
		$sql = "INSERT INTO products (product_name, business_id, description, price, quantity, is_available) 
		        VALUES (?, ?, ?, ?, ?, 'True')";
		$db->executePrepared($sql, $product);
		echo "<div class='success'>✓ Created product: {$product[0]} - \${$product[3]}</div>";
	}
	
	// 5. Insert sample categories
	echo "<h2>Creating Categories...</h2>";
	
	$categories = ['plants', 'indoor', 'tropical', 'variegated', 'rare', 'hanging', 'large', 'easy-care'];
	
	foreach ($categories as $category) {
		$sql = "INSERT INTO categories (category_name) VALUES (?)
		        ON DUPLICATE KEY UPDATE category_name=category_name";
		$db->executePrepared($sql, [$category]);
		echo "<div class='success'>✓ Created category: {$category}</div>";
	}
	
	// 6. Link products to categories
	echo "<h2>Linking Products to Categories...</h2>";
	
	$result = $db->query("SELECT product_id, product_name FROM products");
	$productMap = [];
	foreach ($result as $row) {
		$productMap[$row['product_name']] = $row['product_id'];
	}
	
	$productCategories = [
		['Variegated Monstera Deliciosa', ['plants', 'indoor', 'tropical', 'variegated', 'rare']],
		['Monstera Adansonii', ['plants', 'indoor', 'tropical', 'easy-care']],
		['Philodendron Pink Princess', ['plants', 'indoor', 'tropical', 'variegated', 'rare']],
		['Alocasia Polly', ['plants', 'indoor', 'tropical']],
		['Philodendron Birkin', ['plants', 'indoor', 'variegated']],
		['String of Hearts', ['plants', 'indoor', 'hanging', 'easy-care']],
		['Fiddle Leaf Fig', ['plants', 'indoor', 'large']]
	];
	
	foreach ($productCategories as $pc) {
		$productId = $productMap[$pc[0]];
		foreach ($pc[1] as $category) {
			$sql = "INSERT INTO product_categories (product_id, category_name) VALUES (?, ?)
			        ON DUPLICATE KEY UPDATE product_id=product_id";
			$db->executePrepared($sql, [$productId, $category]);
		}
		echo "<div class='success'>✓ Linked categories to: {$pc[0]}</div>";
	}
	
	// 7. Insert product images
	echo "<h2>Adding Product Images...</h2>";
	
	// Map product names to their IDs for image insertion
	$productImageMap = [
		1 => 'Variegated Monstera Deliciosa',
		2 => 'Monstera Adansonii',
		3 => 'Philodendron Pink Princess',
		4 => 'Alocasia Polly',
		5 => 'Philodendron Birkin',
		6 => 'String of Hearts',
		7 => 'Fiddle Leaf Fig'
	];
	
	foreach ($productImageMap as $displayId => $productName) {
		if (isset($productMap[$productName])) {
			$productId = $productMap[$productName];
			
			// Add feature image
			$sql = "INSERT INTO product_images (product_id, image_url, thumb_url, blur_url, sort_order) 
			        VALUES (?, ?, ?, ?, ?)
			        ON DUPLICATE KEY UPDATE sort_order=sort_order";
			
			$imageUrl = "assets/images/products/{$displayId}/feature.webp";
			$thumbUrl = "assets/images/products/{$displayId}/feature.webp";
			$blurUrl = "assets/images/products/{$displayId}/feature.webp";
			
			$db->executePrepared($sql, [$productId, $imageUrl, $thumbUrl, $blurUrl, 0]);
			
			// Add additional image
			$imageUrl2 = "assets/images/products/{$displayId}/1.webp";
			$thumbUrl2 = "assets/images/products/{$displayId}/1.webp";
			$blurUrl2 = "assets/images/products/{$displayId}/1.webp";
			
			$db->executePrepared($sql, [$productId, $imageUrl2, $thumbUrl2, $blurUrl2, 1]);
			
			echo "<div class='success'>✓ Added images for: {$productName}</div>";
		}
	}
	
	echo "<div class='info'><strong>✅ Sample data inserted successfully!</strong></div>";
	echo "<div class='info'><strong>Test Accounts:</strong><br>
		Username: john.seller | Password: password123<br>
		Username: jane.buyer | Password: password123<br>
		Username: admin | Password: password123
	</div>";
	echo "<a href='index.php' class='btn'>View Website</a>";
	echo " <a href='product/1' class='btn'>View Product 1</a>";
	echo " <a href='business/1' class='btn'>View Business 1</a>";
	echo " <a href='shop' class='btn'>View Shop</a>";
	
} catch (Exception $e) {
	echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

$db->close();

echo "</div></body></html>";
?>
