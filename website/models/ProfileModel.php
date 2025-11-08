<?php
/*
 * Profile Model
 * This model is responsible for handling user profile data and updates.
 */

class ProfileModel extends AbstractModel {
	
	private $userData = [];
	private $errorMessages = [];
	
	// Load user profile data
	public function loadUserProfile($username) {
		$sql = "SELECT username, email, first_name, last_name, account_type, created_at 
		        FROM users 
		        WHERE username = ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$username]);
		
		if (empty($result)) {
			throw new InvalidDataException('User not found');
		}
		
		$this->userData = $result[0];
		return true;
	}
	
	// Update user profile
	public function updateProfile($username, $data) {
		$this->errorMessages = [];
		
		// Validate input
		if (!$this->validateProfileData($data)) {
			return false;
		}
		
		// Update basic info
		$sql = "UPDATE users 
		        SET email = ?, first_name = ?, last_name = ? 
		        WHERE username = ?";
		
		try {
			$this->getDB()->executePrepared($sql, [
				$data['email'],
				$data['first_name'],
				$data['last_name'],
				$username
			]);
			
			// Update password if provided
			if (!empty($data['new_password'])) {
				if (!$this->updatePassword($username, $data['current_password'], $data['new_password'])) {
					return false;
				}
			}
			
			return true;
			
		} catch (DatabaseException $e) {
			$this->errorMessages[] = 'Failed to update profile. Please try again.';
			return false;
		}
	}
	
	// Validate profile data
	private function validateProfileData($data) {
		if (empty($data['first_name'])) {
			$this->errorMessages[] = 'First name is required';
		}
		
		if (empty($data['last_name'])) {
			$this->errorMessages[] = 'Last name is required';
		}
		
		if (empty($data['email'])) {
			$this->errorMessages[] = 'Email is required';
		} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			$this->errorMessages[] = 'Invalid email format';
		}
		
		// Validate password change if provided
		if (!empty($data['new_password']) || !empty($data['current_password']) || !empty($data['confirm_password'])) {
			if (empty($data['current_password'])) {
				$this->errorMessages[] = 'Current password is required to change password';
			}
			
			if (empty($data['new_password'])) {
				$this->errorMessages[] = 'New password is required';
			} elseif (strlen($data['new_password']) < 6) {
				$this->errorMessages[] = 'New password must be at least 6 characters';
			}
			
			if ($data['new_password'] !== $data['confirm_password']) {
				$this->errorMessages[] = 'New passwords do not match';
			}
		}
		
		return empty($this->errorMessages);
	}
	
	// Update password
	private function updatePassword($username, $currentPassword, $newPassword) {
		// Verify current password
		$sql = "SELECT password_hash FROM users WHERE username = ?";
		$result = $this->getDB()->queryPrepared($sql, [$username]);
		
		if (empty($result)) {
			$this->errorMessages[] = 'User not found';
			return false;
		}
		
		$userData = $result[0];
		
		// Verify current password using password_verify
		if (!password_verify($currentPassword, $userData['password_hash'])) {
			$this->errorMessages[] = 'Current password is incorrect';
			return false;
		}
		
		// Hash new password using
		$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
		
		// Update password
		$sql = "UPDATE users SET password_hash = ? WHERE username = ?";
		$this->getDB()->executePrepared($sql, [$newPasswordHash, $username]);
		
		return true;
	}
	
	// Get user data
	public function getUserData() {
		return $this->userData;
	}
	
	// Get error messages
	public function getErrorMessages() {
		return $this->errorMessages;
	}
	
	// Get total orders count
	public function getTotalOrders($username) {
		$sql = "SELECT COUNT(*) as total FROM orders WHERE buyer_username = ? AND status != 'Cart'";
		$result = $this->getDB()->queryPrepared($sql, [$username]);
		return $result[0]['total'] ?? 0;
	}
	
	// Get recent orders
	public function getRecentOrders($username, $limit = 5) {
		$sql = "SELECT o.*, COUNT(oi.product_id) as item_count
		        FROM orders o
		        LEFT JOIN order_items oi ON o.order_id = oi.order_id
		        WHERE o.buyer_username = ? AND o.status != 'Cart'
		        GROUP BY o.order_id
		        ORDER BY o.order_date DESC
		        LIMIT ?";
		
		return $this->getDB()->queryPrepared($sql, [$username, $limit]);
	}
}
?>
