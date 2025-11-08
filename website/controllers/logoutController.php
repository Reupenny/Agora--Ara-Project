<?php
/*
 * Logout Controller
 * This controller is responsible for handling user logout.
 */

include_once 'models/UserModel.php';

class LogoutController extends AbstractController {
	
	protected function getView($isPostback) {
		// Create user object
		$user = new User($this->getContext());
		
		// Destroy session
		$user->destroySession();
		
		// Redirect to home page
		$this->redirectTo('', 'You have been logged out successfully.');
		
		return null;
	}
}
?>
