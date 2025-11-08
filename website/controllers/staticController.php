<?php
/*
 * Static Page Controller
 * This controller is responsible for handling static pages such as the home, about, contact, and privacy pages.
 */

include 'models/StaticModel.php';
include 'views/StaticView.php';

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
