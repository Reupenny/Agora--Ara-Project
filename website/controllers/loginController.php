<?php
/*
	Login Controller
	Handles user authentication
*/

include_once 'models/login.php';
include_once 'models/user.php';
include_once 'views/login.php';

class LoginController extends AbstractController {
	
	protected function getView($isPostback) {
		// Check if user is already logged in
		$user = new User($this->getContext());
		if ($user->isLoggedIn()) {
			$this->redirectTo('profile', '');
			return null;
		}
		
		// If POST request, handle login
		if ($isPostback) {
			return $this->handleLogin();
		}
		
		// Otherwise, show login form
		$view = new LoginView();
		$view->setErrorMessage('');
		
		return $view;
	}
	
	private function handleLogin() {
		// Get credentials from POST
		$username = $_POST['username'] ?? '';
		$password = $_POST['password'] ?? '';
		
		// Validate credentials
		$model = new LoginModel($this->getDB());
		
		if ($model->validateCredentials($username, $password)) {
			// Create user session
			$user = new User($this->getContext());
			$user->createSession($model->getUsername());
			
			// Redirect to home page
			$this->redirectTo('', 'Welcome back, ' . $user->getFirstName() . '!');
			return null;
		} else {
			// Show error on login form
			$view = new LoginView();
			$view->setErrorMessage($model->getErrorMessage());
			$view->setUsername($username);
			
			return $view;
		}
	}
}
?>
