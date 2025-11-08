<?php
/*
 * Login Model
 * This model is responsible for handling user authentication.
 */

class LoginModel extends AbstractModel {
	
	private $errorMessage = '';
	private $username = '';
	
	// Validate user credentials
	public function validateCredentials($username, $password) {
		// Get user data from database
		$sql = "SELECT username, password_hash 
		        FROM users 
		        WHERE username = ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$username]);
		
		if (empty($result)) {
			$this->errorMessage = 'Invalid username or password';
			return false;
		}
		
		$userData = $result[0];
		
		// Verify password using PHP's password_verify
		if (!password_verify($password, $userData['password_hash'])) {
			$this->errorMessage = 'Invalid username or password';
			return false;
		}
		
		// Store username for session creation
		$this->username = $userData['username'];
		return true;
	}
	
	// Get error message
	public function getErrorMessage() {
		return $this->errorMessage;
	}
	
	// Get validated username
	public function getUsername() {
		return $this->username;
	}
}
?>
