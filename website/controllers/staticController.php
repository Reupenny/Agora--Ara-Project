<?php
/*
	Static Page Controller
	Handles home, about, contact, privacy pages
*/

include 'models/static.php';
include 'views/static.php';

class StaticController extends AbstractController {
	
	protected function getView($isPostback) {
		// Get the page name from URI, default to 'home'
		$pageName = $this->getURI()->getPart();
		if ($pageName === '') {
			$pageName = 'home';
		}
		
		// Validate page name (only allow specific static pages)
		$validPages = ['profile', 'about', 'register', 'login'];
		if (!in_array($pageName, $validPages)) {
			throw new InvalidRequestException('Invalid static page: ' . $pageName);
		}
		
		// Create model and view
		$model = new StaticModel($this->getDB());
		$model->setPageName($pageName);
		
		$view = new StaticView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
