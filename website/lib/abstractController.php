<?php

abstract class AbstractController {
	
	private $context;
	private $redirect;
	
	public function __construct (IContext $context){
		$this->context=$context;
		$this->redirect=null;
	}
	protected function getContext() {
		return $this->context;
	}
	protected function getDB() {
		return $this->context->getDB();
	}
	protected function getURI() {
		return $this->context->getURI();
	}
	protected function getConfig() {
		return $this->context->getConfig();
	}
	protected function getSession() {
		return $this->context->getSession();
	}

	public function process() {
		$method=$_SERVER['REQUEST_METHOD'];
		switch($method) {
			case 'GET':  	$view=$this->getView(false);	break;
			case 'POST':  	$view=$this->getView(true);		break;
			default:
				throw new InvalidRequestException ("Invalid Request verb");
		}
		if ($view!==null) {
			$view->prepare();
			// apply global template arguments
			$site=$this->getURI()->getSite();
			$view->setTemplateField('site',$site);
			
			// Set navigation links based on login status
			$user = $this->getContext()->getUser();
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
				$view->setTemplateField('user_profile_pic', $profilePicHtml);
				
				// Build navigation based on account type
				$navLinks = '';
				$mobileNavLinks = '                    <br />' . "\n";
				
				// Buyer-specific links
				if ($user->isBuyer()) {
					$navLinks .= '<a href="' . $site . 'cart">Cart</a>' . "\n";
					$navLinks .= '                ';
					$mobileNavLinks .= '<a href="' . $site . 'cart">Cart</a>' . "\n";
					$mobileNavLinks .= '                    ';
				}
				
				// Seller-specific links
				if ($user->isSeller()) {
					$mobileNavLinks .= '<a href="' . $site . 'product-add">Add Product</a>' . "\n";
					$mobileNavLinks .= '                    <a href="' . $site . 'seller-orders">Manage Orders</a>' . "\n";
					$mobileNavLinks .= '                    <a href="' . $site . 'business-manage">Manage Business</a>' . "\n";
					$mobileNavLinks .= '                    ';
				}
				
				// Admin-specific links
				if ($user->isAdmin()) {
					$navLinks .= '<a href="' . $site . 'admin-panel">Admin Panel</a>' . "\n";
					$navLinks .= '                ';
					$mobileNavLinks .= '<a href="' . $site . 'admin-panel">Admin Panel</a>' . "\n";
					$mobileNavLinks .= '                    ';
				}
				
				// Common links for all logged-in users
				$mobileNavLinks .= '<a href="' . $site . 'profile">Profile</a>' . "\n";
				$mobileNavLinks .= '                    <br />' . "\n";
				$mobileNavLinks .= '                    ';
				
				$navLinks .= '<a href="' . $site . 'logout">Logout</a>';
				$mobileNavLinks .= '<a href="' . $site . 'logout">Logout</a>';
				
				$view->setTemplateField('nav_links', $navLinks);
				$view->setTemplateField('mobile_nav_links', $mobileNavLinks);
			} else {
				// User is not logged in - no profile picture
				$view->setTemplateField('user_profile_pic', '');
				
				// User is not logged in - show Register and Login
				$navLinks = '<a href="' . $site . 'register">Register</a>' . "\n";
				$navLinks .= '                <a href="' . $site . 'login">Login</a>';
				$view->setTemplateField('nav_links', $navLinks);
				
				$mobileNavLinks = '                    <br />' . "\n";
				$mobileNavLinks .= '<a href="' . $site . 'login">Login</a>' . "\n";
				$mobileNavLinks .= '                    <a href="' . $site . 'register">Register</a>';
				$view->setTemplateField('mobile_nav_links', $mobileNavLinks);
			}
			
			$view->render();
		} elseif ($this->redirect!==null) {
			header ('Location: '.$this->redirect);
		} else {
			throw new InvalidRequestException ("View not set");
		}
	}

	// sub-controllers will override this
	protected function getView($isPostback) {
		return null;
	}	
	
	protected function redirectTo ($page, $feedback = '') {
		// Store feedback message in session if provided
		if (!empty($feedback)) {
			$this->getSession()->set('feedback', $feedback);
		}
		$this->redirect = $this->getURI()->getSite() . $page;
	}
}
?>
