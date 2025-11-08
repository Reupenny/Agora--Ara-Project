<?php
/*
 * User Model
 * This model is responsible for managing user state and authentication.
 */

class User {
	private $context;
	private $db;
	private $session;
	
	private $username;
	private $firstName;
	private $lastName;
	private $email;
	private $accountType;
	private $isLoggedIn;
	
	public function __construct($context) {
		$this->context = $context;
		$this->db = $context->getDB();
		$this->session = $context->getSession();
		$this->isLoggedIn = false;
		
		// Check if user is logged in via session
		if ($this->session->isKeySet('username')) {
			$this->loadFromSession();
		}
	}
	
	// Load user data from session
	private function loadFromSession() {
		$this->username = $this->session->get('username');
		$this->firstName = $this->session->get('first_name');
		$this->lastName = $this->session->get('last_name');
		$this->email = $this->session->get('email');
		$this->accountType = $this->session->get('account_type');
		$this->isLoggedIn = true;
	}
	
	// Create session for user
	public function createSession($username) {
		// Load user data from database
		$sql = "SELECT username, email, first_name, last_name, account_type 
		        FROM users 
		        WHERE username = ?";
		
		$result = $this->db->queryPrepared($sql, [$username]);
		
		if (empty($result)) {
			throw new AuthenticationException('User not found');
		}
		
		$userData = $result[0];
		
		// Store in session
		$this->session->changeContext(); // Regenerate session ID for security
		$this->session->set('username', $userData['username']);
		$this->session->set('first_name', $userData['first_name']);
		$this->session->set('last_name', $userData['last_name']);
		$this->session->set('email', $userData['email']);
		$this->session->set('account_type', $userData['account_type']);
		
		// Update local properties
		$this->username = $userData['username'];
		$this->firstName = $userData['first_name'];
		$this->lastName = $userData['last_name'];
		$this->email = $userData['email'];
		$this->accountType = $userData['account_type'];
		$this->isLoggedIn = true;
	}
	
	// Destroy user session (logout)
	public function destroySession() {
		$this->session->clear();
		$this->isLoggedIn = false;
		$this->username = null;
		$this->firstName = null;
		$this->lastName = null;
		$this->email = null;
		$this->accountType = null;
	}
	
	// Getters
	public function isLoggedIn() {
		return $this->isLoggedIn;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getFirstName() {
		return $this->firstName;
	}
	
	public function getLastName() {
		return $this->lastName;
	}
	
	public function getFullName() {
		return $this->firstName . ' ' . $this->lastName;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function getAccountType() {
		return $this->accountType;
	}
	
	public function isBuyer() {
		return $this->accountType === 'Buyer';
	}
	
	public function isSeller() {
		return $this->accountType === 'Seller';
	}
	
	public function isAdmin() {
		return $this->accountType === 'Agora Admin';
	}
}
?>
