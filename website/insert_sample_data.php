<?php
/*
	Database Sample Data Insertion Script
	=====================
	Run this file once to insert sample data into the agora_db database
	Access via: http://localhost:8000/insert_sample_data.php
*/

// Database connection settings

$configText = file_get_contents('config/website.conf');
$config = json_decode($configText, true);

$host = $config['db']['dbHost'];
$user = $config['db']['dbUser'];
$password = $config['db']['dbPassword'];
$database = $config['db']['dbDatabase'];

echo "<h1>Agora Sample Data Insertion</h1>";
echo "<pre>";

try {
	// First, connect without selecting a database
	echo "Connecting to MySQL...\n";
	$conn = new mysqli($host, $user, $password);
	
	if ($conn->connect_error) {
		throw new Exception("Connection failed: " . $conn->connect_error);
	}
	echo "✓ Connected to MySQL successfully\n\n";
	
	// Select the database
	$conn->select_db($database);
	echo "Using database '$database'\n\n";
	
	// Read the SQL file
	$sqlFile = 'config/sampleData.sql';
	if (!file_exists($sqlFile)) {
		throw new Exception("SQL file not found: $sqlFile");
	}
	
	echo "Reading SQL file: $sqlFile\n";
	$sqlContent = file_get_contents($sqlFile);
	
	// Remove comment lines first
	$lines = explode("\n", $sqlContent);
	$cleanedLines = array();
	foreach ($lines as $line) {
		$line = trim($line);
		// Skip empty lines and full comment lines
		if (empty($line) || substr($line, 0, 2) === '--') continue;
		$cleanedLines[] = $line;
	}
	$sqlContent = implode("\n", $cleanedLines);
	
	// Split by semicolons to get individual statements
	$statements = explode(';', $sqlContent);
	
	// Filter out empty statements and unwanted commands
	$validStatements = array();
	foreach ($statements as $statement) {
		$statement = trim($statement);
		// Skip empty statements
		if (empty($statement)) continue;
		// Skip DROP DATABASE, CREATE DATABASE, and USE commands (we already did those)
		if (preg_match('/^(drop database|CREATE DATABASE|USE)\s+/i', $statement)) continue;
		
		$validStatements[] = $statement;
	}
	$statements = $validStatements;
	
	echo "Found " . count($statements) . " SQL statements to execute\n\n";
	
	// Execute each statement
	$successCount = 0;
	foreach ($statements as $index => $statement) {
		if (empty(trim($statement))) continue;
		
		// Get table name for display
		if (preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches)) {
			$tableName = $matches[1];
			echo "Creating table: $tableName...";
			
			if ($conn->query($statement) === TRUE) {
				echo " ✓\n";
				$successCount++;
			} else {
				echo " ✗\n";
				echo "  Error: " . $conn->error . "\n";
			}
		} else {
			// Execute other statements without displaying
			if ($conn->query($statement) === TRUE) {
				$successCount++;
			}
		}
	}
	
	echo "\n";
	echo "=========================================\n";
	echo "Sample data insertion complete!\n";
	echo "$successCount statements executed\n";
	echo "=========================================\n\n";
	
	// Show created tables
	echo "Tables in database:\n";
	$result = $conn->query("SHOW TABLES");
	while ($row = $result->fetch_array()) {
		echo "  • " . $row[0] . "\n";
	}
	
	echo "\n✓ Database setup complete!\n";
	echo "\nYou can now use the Agora application.\n";
	echo "Go to: <a href='http://localhost:8000/'>http://localhost:8000/</a>\n";
	
	$conn->close();
	
} catch (Exception $e) {
	echo "\n✗ Error: " . $e->getMessage() . "\n";
	echo "\nPlease check:\n";
	echo "1. MySQL is running\n";
	echo "2. Username and password are correct\n";
    echo "3. Sample data has not been inserted\n";
	echo "4. SQL file exists at: config/sampleData.sql\n";
}

echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
	<title>Agora Database Setup</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			max-width: 800px;
			margin: 50px auto;
			padding: 20px;
			background-color: #f5f5f5;
		}
		h1 {
			color: #333;
		}
		pre {
			background-color: #fff;
			padding: 20px;
			border-radius: 5px;
			border: 1px solid #ddd;
			overflow-x: auto;
		}
		a {
			color: #0066cc;
			text-decoration: none;
		}
		a:hover {
			text-decoration: underline;
		}
	</style>
</head>
<body>
</body>
</html>
