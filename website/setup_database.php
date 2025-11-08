<?php
/*
	Database Setup Script
	=====================
	Run this file once to create the agora_db database and all tables
	Access via: http://localhost:8000/setup_database.php
*/


$configText = file_get_contents('config/website.conf');
$config = json_decode($configText, true);

$host = $config['db']['dbHost'];
$user = $config['db']['dbUser'];
$password = $config['db']['dbPassword'];
$database = $config['db']['dbDatabase'];

echo "<h1>Agora Database Setup</h1>";
echo "<pre>";

try {
	echo "Connecting to MySQL...\n";
	$conn = new mysqli($host, $user, $password);
	
	if ($conn->connect_error) {
		throw new Exception("Connection failed: " . $conn->connect_error);
	}
	echo "✓ Connected to MySQL successfully\n\n";
	
	echo "Creating database '$database'...\n";
	$sql = "CREATE DATABASE IF NOT EXISTS $database";
	if ($conn->query($sql) === TRUE) {
		echo "✓ Database '$database' created or already exists\n\n";
	} else {
		throw new Exception("Error creating database: " . $conn->error);
	}
	
	$conn->select_db($database);
	echo "Using database '$database'\n\n";
	
	$sqlFile = 'config/createDatabase.sql';
	if (!file_exists($sqlFile)) {
		throw new Exception("SQL file not found: $sqlFile");
	}
	
	echo "Reading SQL file: $sqlFile\n";
	$sqlContent = file_get_contents($sqlFile);
	
	$lines = explode("\n", $sqlContent);
	$cleanedLines = array();
	foreach ($lines as $line) {
		$line = trim($line);
		if (empty($line) || substr($line, 0, 2) === '--') continue;
		$cleanedLines[] = $line;
	}
	$sqlContent = implode("\n", $cleanedLines);
	
	$statements = explode(';', $sqlContent);
	
	$validStatements = array();
	foreach ($statements as $statement) {
		$statement = trim($statement);
		if (empty($statement)) continue;
		if (preg_match('/^(drop database|CREATE DATABASE|USE)\s+/i', $statement)) continue;
		
		$validStatements[] = $statement;
	}
	$statements = $validStatements;
	
	echo "Found " . count($statements) . " SQL statements to execute\n\n";
	
	$successCount = 0;
	foreach ($statements as $index => $statement) {
		if (empty(trim($statement))) continue;
		
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
			if ($conn->query($statement) === TRUE) {
				$successCount++;
			}
		}
	}
	
	echo "\n";
	echo "=========================================\n";
	echo "Setup completed successfully!\n";
	echo "$successCount statements executed\n";
	echo "=========================================\n\n";
	
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
	echo "3. SQL file exists at: config/createDatabase.sql\n";
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
