<?php
/*
	Error View
	Renders error pages using the master template
*/

class ErrorView extends AbstractView {
	
	private $errorCode;
	private $errorTitle;
	private $errorMessage;
	private $context;
	
	public function __construct($errorCode, $errorTitle, $errorMessage, $context = null) {
		parent::__construct();
		$this->errorCode = $errorCode;
		$this->errorTitle = $errorTitle;
		$this->errorMessage = $errorMessage;
		$this->context = $context;
	}
	
	public function prepare() {
		// Set page title
		$this->setTemplateField('pagename', $this->errorCode . ' - ' . $this->errorTitle);
		
		// Set site root for ##site## token
		if ($this->context !== null) {
			$site = $this->context->getURI()->getSite();
			$this->setTemplateField('site', $site);
		} else {
			$site = '/';
			$this->setTemplateField('site', $site);
		}
		
		// Set navigation links based on login status
		$user = null;
		if ($this->context !== null) {
			$user = $this->context->getUser();
		}
		
		if ($user && $user->isLoggedIn()) {
			// Get profile picture
			$username = $user->getUsername();
			$profileImagePath = 'assets/images/users/' . $username . '.webp';
			if (file_exists($profileImagePath)) {
				$profileImageUrl = $site . $profileImagePath;
			} else {
				// Default avatar
				$profileImageUrl = $site . 'assets/images/users/default-avatar.svg';
			}
			
			// Add profile picture to navigation
			$profilePicHtml = '<a class="profile-pic" href="' . $site . 'profile" >' . "\n";
			$profilePicHtml .= '                    <img src="' . $profileImageUrl . '" alt="Profile">' . "\n";
			$profilePicHtml .= '                </a>';
			$this->setTemplateField('user_profile_pic', $profilePicHtml);
			
			// User is logged in - show Profile and Logout
			$navLinks = '                <a href="' . $site . 'logout">Logout</a>';
			$this->setTemplateField('nav_links', $navLinks);
			
			$mobileNavLinks = '<a href="' . $site . 'product">Add Product</a>' . "\n";
			$mobileNavLinks .= '                    <a href="' . $site . 'profile">Profile</a>' . "\n";
			$mobileNavLinks .= '                    <a href="' . $site . 'business">Business</a>' . "\n";
			$mobileNavLinks .= '                    <br />' . "\n";
			$mobileNavLinks .= '                    <a href="' . $site . 'logout">Logout</a>';
			$this->setTemplateField('mobile_nav_links', $mobileNavLinks);
		} else {
			// User is not logged in - no profile picture
			$this->setTemplateField('user_profile_pic', '');
			
			// User is not logged in - show Register and Login
			$navLinks = '<a href="' . $site . 'register">Register</a>' . "\n";
			$navLinks .= '                <a href="' . $site . 'login">Login</a>';
			$this->setTemplateField('nav_links', $navLinks);
			
			$mobileNavLinks = '<a href="' . $site . 'login">Login</a>' . "\n";
			$mobileNavLinks .= '                    <a href="' . $site . 'register">Register</a>';
			$this->setTemplateField('mobile_nav_links', $mobileNavLinks);
		}
		
		// Load the appropriate error template
		$templateFile = 'html/error/error-' . $this->errorCode . '.html';
		if (!file_exists($templateFile)) {
			$templateFile = 'html/error/error-500.html'; // Fallback to 500 error
		}
		
		$content = file_get_contents($templateFile);
		
		// Replace tokens in error content
		$content = str_replace('##error_message##', htmlspecialchars($this->errorMessage), $content);
		$content = str_replace('##site##', $site, $content);
		
		$this->setTemplateField('content', $content);
		
		// Set the master template
		$this->setTemplate('html/masterPage.html');
	}
}
?>
