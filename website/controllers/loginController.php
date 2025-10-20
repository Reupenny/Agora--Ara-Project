<?php
/*
	Login Controller
	Handles user authentication
*/

include 'models/static.php';
include 'views/static.php';

class LoginController extends AbstractController {
	
	protected function getView($isPostback) {
		// If POST request, handle login
		if ($isPostback) {
			return $this->handleLogin();
		}
		
		// Otherwise, show login form
		$model = new StaticModel($this->getDB());
		$model->setPageName('login');
		
		$view = new StaticView();
		$view->setModel($model);
		
		return $view;
	}
	
	private function handleLogin() {
		// TODO: Implement login logic
		// 1. Get username/email and password from $_POST
		// 2. Validate credentials against database
		// 3. Create session if valid
		// 4. Redirect to profile/home
		// 5. Show error if invalid
		
		// For now, just show the login form again
		$model = new StaticModel($this->getDB());
		$model->setPageName('login');
		
		$view = new StaticView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
