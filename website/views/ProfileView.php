<?php
/*
	Profile View
	Displays the user profile page
*/

class ProfileView extends AbstractView {
	
	private $errorMessages = [];
	private $successMessage = '';
	private $formData = [];
	private $user = null;
	
	public function setErrorMessages($messages) {
		$this->errorMessages = $messages;
	}
	
	public function setSuccessMessage($message) {
		$this->successMessage = $message;
	}
	
	public function setFormData($data) {
		$this->formData = $data;
	}
	
	public function setUser($user) {
		$this->user = $user;
	}
	
	public function prepare() {
		// Set master page template
		$this->setTemplate('html/masterPage.html');
		
		// Set master page fields
		$this->setTemplateField('pagename', 'Profile');
		$this->setTemplateField('site', $this->getSiteURL());
		
		// Load profile content
		$profileContent = file_get_contents('html/profile.html');
		
		// Replace site URL in the content
		$profileContent = str_replace('##site##', $this->getSiteURL(), $profileContent);
		
		// Get user data from model
		$model = $this->getModel();
		$userData = $model->getUserData();
		
		// Use form data if available (for repopulation after error), otherwise use DB data
		$firstName = $this->formData['first_name'] ?? $userData['first_name'];
		$lastName = $this->formData['last_name'] ?? $userData['last_name'];
		$email = $this->formData['email'] ?? $userData['email'];
		$username = $userData['username'];
		$accountType = $userData['account_type'];
		$memberSince = date('M Y', strtotime($userData['created_at']));
		
		// Replace template tokens
		$profileContent = str_replace('##first_name_value##', htmlspecialchars($firstName), $profileContent);
		$profileContent = str_replace('##last_name_value##', htmlspecialchars($lastName), $profileContent);
		$profileContent = str_replace('##email_value##', htmlspecialchars($email), $profileContent);
		$profileContent = str_replace('##username_value##', htmlspecialchars($username), $profileContent);
		$profileContent = str_replace('##join_date##', htmlspecialchars($memberSince), $profileContent);
		$profileContent = str_replace('##total_orders##', $model->getTotalOrders($username), $profileContent);
		$profileContent = str_replace('##account_type##', htmlspecialchars($accountType), $profileContent);
		$profileContent = str_replace('##profile_description##', '', $profileContent); // TODO: Add bio field to database
		
		// Generate orders section (buyers only)
		$ordersHtml = '';
		if ($accountType === 'Buyer') {
			$recentOrders = $model->getRecentOrders($username, 5);
			
			if (!empty($recentOrders)) {
				$ordersHtml = '<div class="order-history-section">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
						<h2 style="margin: 0;">Recent Orders</h2>
						<a href="##site##orders" class="btn-secondary" style="text-decoration: none;">View All Orders</a>
					</div>
					<div class="orders-grid">';
				
				foreach ($recentOrders as $order) {
					$statusClass = strtolower($order['status']);
					$statusText = htmlspecialchars($order['status']);
					$orderDate = date('F j, Y', strtotime($order['order_date']));
					$orderTotal = $order['total_amount'] ? '$' . number_format($order['total_amount'], 2) : 'TBD';
					$itemCount = $order['item_count'];
					
					// Edit button only for pending orders
					$editButton = '';
					if ($order['status'] === 'Pending') {
						$editButton = '<a href="##site##order/' . $order['order_id'] . '" class="btn-secondary" style="font-size: 0.9rem; padding: 5px 10px; text-decoration: none;">Edit Order</a>';
					}
					
					$ordersHtml .= '<div class="order-card">
						<div class="order-header">
							<h3>Order #' . $order['order_id'] . '</h3>
							<span class="order-status ' . $statusClass . '">' . $statusText . '</span>
						</div>
						<div class="order-details">
							<p>Date: ' . $orderDate . '</p>
							<p>Total: ' . $orderTotal . '</p>
							<p>Items: ' . $itemCount . '</p>
							' . $editButton . '
						</div>
					</div>';
				}
				
				$ordersHtml .= '</div></div>';
			} else {
				$ordersHtml = '<div class="order-history-section">
					<h2>Recent Orders</h2>
					<p style="text-align: center; padding: 40px; color: #666;">You haven\'t placed any orders yet. <a href="##site##shop">Start shopping</a></p>
				</div>';
			}
		}

		// Ensure any '##site##' tokens inside the dynamically generated orders HTML are replaced
		if (!empty($ordersHtml)) {
			$ordersHtml = str_replace('##site##', $this->getSiteURL(), $ordersHtml);
		}
		
		// Replace or remove the orders section in the template
		if (strpos($profileContent, '<div class="order-history-section">') !== false) {
			// Replace the existing static orders section with dynamic content
			$profileContent = preg_replace(
				'/<div class="order-history-section">.*?<\/div>\s*<\/div>/s',
				$ordersHtml,
				$profileContent
			);
		} else {
			// Append orders section if not in template
			$profileContent .= $ordersHtml;
		}

		// Remove any leftover placeholder tag markers like ##Orders## (safety fallback)
		$profileContent = str_replace('##Orders##', '', $profileContent);
		
		// Get profile image URL
		$profileImagePath = 'assets/images/users/' . $username . '.webp';
		if (file_exists($profileImagePath)) {
			$profileImageUrl = $this->getSiteURL() . $profileImagePath;
		} else {
			// Default avatar
			$profileImageUrl = $this->getSiteURL() . 'assets/images/users/default-avatar.svg';
		}
		$profileContent = str_replace('##profile_image_url##', $profileImageUrl, $profileContent);
		
		// Add error messages if present
		if (!empty($this->errorMessages)) {
			$errorHtml = '<div class="error-message" style="background-color: #fee; border: 1px solid #fcc; padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #c00;"><ul style="margin: 0; padding-left: 20px;">';
			foreach ($this->errorMessages as $error) {
				$errorHtml .= '<li>' . htmlspecialchars($error) . '</li>';
			}
			$errorHtml .= '</ul></div>';
			
			// Insert error messages after the <h1>
			$profileContent = preg_replace('/(<h1>.*?<\/h1>)/', '$1' . "\n" . $errorHtml, $profileContent, 1);
		}
		
		// Add success message if present
		if (!empty($this->successMessage)) {
			$successHtml = '<div class="success-message" style="background-color: #efe; border: 1px solid #cfc; padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #060;">' 
			             . htmlspecialchars($this->successMessage) 
			             . '</div>';
			
			// Insert success message after the <h1>
			$profileContent = preg_replace('/(<h1>.*?<\/h1>)/', '$1' . "\n" . $successHtml, $profileContent, 1);
		}
		
		// Set the content field
		$this->setTemplateField('content', $profileContent);
	}
	
	private function getSiteURL() {
		// Get base URL from context
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'];
		$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
		return $protocol . '://' . $host . $scriptDir . '/';
	}
}
?>
