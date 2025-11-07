<?php
/*
	Businesses Controller
	Handles businesses listing/directory page
*/

include 'models/businesses.php';
include 'views/businesses.php';

class BusinessesController extends AbstractController {
	
	protected function getView($isPostback) {
		// Create model
		$model = new BusinessesModel($this->getDB());
		
		// Load businesses
		$model->loadBusinesses();
		
		// Create view
		$view = new BusinessesView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
