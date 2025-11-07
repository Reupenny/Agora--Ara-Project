<?php
/*
	Login View
	Displays the login form
*/

class LoginView extends AbstractView {
	
	private $errorMessage = '';
	private $username = '';
	
	public function setErrorMessage($message) {
		$this->errorMessage = $message;
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function prepare() {
		// Set master page template
		$this->setTemplate('html/masterPage.html');
		
		// Set master page fields
		$this->setTemplateField('pagename', 'Login');
		$this->setTemplateField('site', $this->getSiteURL());
		
		// Load login form content
		$loginContent = file_get_contents('html/login.html');
		
		// Replace site URL in the content
		$loginContent = str_replace('##site##', $this->getSiteURL(), $loginContent);
		
		// Replace username value
		$loginContent = str_replace('##username_value##', htmlspecialchars($this->username), $loginContent);
		
		// Set error message if present
		if (!empty($this->errorMessage)) {
			$errorHtml = '<div class="error-message" style="background-color: #fee; border: 1px solid #fcc; padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #c00;">' 
			           . htmlspecialchars($this->errorMessage) 
			           . '</div>';
			$loginContent = str_replace('##error##', $errorHtml, $loginContent);
		} else {
			$loginContent = str_replace('##error##', '', $loginContent);
		}
		
		// Set the content field
		$this->setTemplateField('content', $loginContent);
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
