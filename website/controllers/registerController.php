<?php
/*
	Register Controller
	Handles new user registration
*/

include 'models/static.php';
include 'views/static.php';

class RegisterController extends AbstractController {
	
	protected function getView($isPostback) {
		// If POST request, handle registration
		if ($isPostback) {
			return $this->handleRegistration();
		}
		
		// Otherwise, show registration form
		$model = new StaticModel($this->getDB());
		$model->setPageName('register');
		
		$view = new StaticView();
		$view->setModel($model);
		
		return $view;
	}
	
	private function handleRegistration() {
		// TODO: Implement registration logic
		// 1. Get form data from $_POST (username, email, password, etc.)
		// 2. Validate input (check required fields, password strength)
		// 3. Check if username/email already exists
		// 4. Hash password
		// 5. Insert new user into database
		// 6. Create session and log user in
		// 7. Redirect to profile/home
		// 8. Show errors if validation fails
		
		// For now, just show the registration form again
		$model = new StaticModel($this->getDB());
		$model->setPageName('register');
		
		$view = new StaticView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
