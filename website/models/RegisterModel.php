<?php
/*
 * Register Model
 * This model is responsible for handling new user registration.
 */

class RegisterModel extends AbstractModel {
	
	private $errorMessages = [];
	private $username = '';
	
	// Register a new user
	public function registerUser($data) {
		// Validate input
		if (!$this->validateInput($data)) {
			return false;
		}
		
		// Check if username already exists
		if ($this->usernameExists($data['username'])) {
			$this->errorMessages[] = 'Username already exists';
			return false;
		}
		
		// Check if email already exists with same account type
		if ($this->emailExists($data['email'], $data['account_type'])) {
			$this->errorMessages[] = 'Email already registered for this account type';
			return false;
		}
		
		// Hash password
		$passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
		
		// Insert into database
		$sql = "INSERT INTO users (username, email, first_name, last_name, password_hash, account_type) 
		        VALUES (?, ?, ?, ?, ?, ?)";
		
		try {
			$this->getDB()->executePrepared($sql, [
				$data['username'],
				$data['email'],
				$data['first_name'],
				$data['last_name'],
				$passwordHash,
				$data['account_type']
			]);
			
			$this->username = $data['username'];
			return true;
			
		} catch (DatabaseException $e) {
			$this->errorMessages[] = 'Failed to create account. Please try again.';
			return false;
		}
	}
	
	// Validate user input
	private function validateInput($data) {
		$this->errorMessages = [];
		
		// Check required fields
		if (empty($data['username'])) {
			$this->errorMessages[] = 'Username is required';
		}
		
		if (empty($data['email'])) {
			$this->errorMessages[] = 'Email is required';
		} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			$this->errorMessages[] = 'Invalid email format';
		}
		
		if (empty($data['first_name'])) {
			$this->errorMessages[] = 'First name is required';
		}
		
		if (empty($data['last_name'])) {
			$this->errorMessages[] = 'Last name is required';
		}
		
		if (empty($data['password'])) {
			$this->errorMessages[] = 'Password is required';
		} elseif (strlen($data['password']) < 6) {
			$this->errorMessages[] = 'Password must be at least 6 characters';
		}
		
		if ($data['password'] !== $data['password_confirm']) {
			$this->errorMessages[] = 'Passwords do not match';
		}
		
		if (empty($data['account_type']) || !in_array($data['account_type'], ['Buyer', 'Seller'])) {
			$this->errorMessages[] = 'Please select an account type';
		}
		
		return empty($this->errorMessages);
	}
	
	// Check if username exists
	private function usernameExists($username) {
		$sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
		$result = $this->getDB()->queryPrepared($sql, [$username]);
		return $result[0]['count'] > 0;
	}
	
	// Check if email exists for this account type
	private function emailExists($email, $accountType) {
		$sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND account_type = ?";
		$result = $this->getDB()->queryPrepared($sql, [$email, $accountType]);
		return $result[0]['count'] > 0;
	}
	
	// Get error messages
	public function getErrorMessages() {
		return $this->errorMessages;
	}
	
	// Get registered username
	public function getUsername() {
		return $this->username;
	}
}
?>
