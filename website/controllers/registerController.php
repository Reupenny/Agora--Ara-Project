<?php
/*
 * Register Controller
 * This controller is responsible for handling new user registration.
 */

include_once 'models/RegisterModel.php';
include_once 'models/UserModel.php';
include_once 'views/RegisterView.php';

class RegisterController extends AbstractController {
	
	protected function getView($isPostback) {
		// Check if user is already logged in
		$user = new User($this->getContext());
		if ($user->isLoggedIn()) {
			$this->redirectTo('profile', '');
			return null;
		}
		
		// If POST request, handle registration
		if ($isPostback) {
			return $this->handleRegistration();
		}
		
		// Otherwise, show registration form
		$view = new RegisterView();
		$view->setErrorMessages([]);
		
		return $view;
	}
	
	private function handleRegistration() {
		// Get form data from POST
		$data = [
			'username' => $_POST['username'] ?? '',
			'email' => $_POST['email'] ?? '',
			'first_name' => $_POST['First-Name'] ?? '',
			'last_name' => $_POST['Last-Name'] ?? '',
			'password' => $_POST['password'] ?? '',
			'password_confirm' => $_POST['password-conf'] ?? '',
			'account_type' => ucfirst($_POST['Account-Type'] ?? '')
		];
		
		// Attempt to register user
		$model = new RegisterModel($this->getDB());
		
		if ($model->registerUser($data)) {
			// Create user session
			$user = new User($this->getContext());
			$user->createSession($model->getUsername());
			
			// Redirect based on account type
			if ($data['account_type'] === 'Seller') {
				$this->redirectTo('profile', 'Registration successful! As a seller, you need to create or join a business before adding products.');
			} else {
				$this->redirectTo('', 'Welcome to Agora, ' . $data['first_name'] . '!');
			}
			return null;
		} else {
			// Show errors on registration form
			$view = new RegisterView();
			$view->setErrorMessages($model->getErrorMessages());
			$view->setFormData($data);
			
			return $view;
		}
	}
}
?>
