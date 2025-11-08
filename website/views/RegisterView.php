<?php
/*
 * Register View
 * This view is responsible for displaying the registration form.
 */

class RegisterView extends AbstractView {
	
	private $errorMessages = [];
	private $formData = [];
	
	public function setErrorMessages($messages) {
		$this->errorMessages = $messages;
	}
	
	public function setFormData($data) {
		$this->formData = $data;
	}
	
	public function prepare() {
		// Set master page template
		$this->setTemplate('html/masterPage.html');
		
		// Set master page fields
		$this->setTemplateField('pagename', 'Register');
		$this->setTemplateField('site', $this->getSiteURL());
		
		// Load register form content
		$registerContent = file_get_contents('html/register.html');
		
		// Replace site URL in the content
		$registerContent = str_replace('##site##', $this->getSiteURL(), $registerContent);
		
		// Set error messages if present
		if (!empty($this->errorMessages)) {
			$errorHtml = '<div class="error-message"><ul style="margin: 0; padding-left: 20px;">';
			foreach ($this->errorMessages as $error) {
				$errorHtml .= '<li>' . htmlspecialchars($error) . '</li>';
			}
			$errorHtml .= '</ul></div>';
			$registerContent = str_replace('##error##', $errorHtml, $registerContent);
		} else {
			$registerContent = str_replace('##error##', '', $registerContent);
		}
		
		// Repopulate form data if provided
		$registerContent = str_replace('##first_name_value##', htmlspecialchars($this->formData['first_name'] ?? ''), $registerContent);
		$registerContent = str_replace('##last_name_value##', htmlspecialchars($this->formData['last_name'] ?? ''), $registerContent);
		$registerContent = str_replace('##email_value##', htmlspecialchars($this->formData['email'] ?? ''), $registerContent);
		$registerContent = str_replace('##username_value##', htmlspecialchars($this->formData['username'] ?? ''), $registerContent);
		
		// Set checked state for radio buttons
		$accountType = $this->formData['account_type'] ?? 'Buyer';
		$registerContent = str_replace('##buyer_checked##', $accountType === 'Buyer' ? 'checked' : '', $registerContent);
		$registerContent = str_replace('##seller_checked##', $accountType === 'Seller' ? 'checked' : '', $registerContent);
		
		// Set the content field
		$this->setTemplateField('content', $registerContent);
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
