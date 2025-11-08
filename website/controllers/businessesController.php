<?php
/*
 * Businesses Controller
 * This controller handles the display of the business listing/directory page.
 */

include 'models/BusinessesModel.php';
include 'views/BusinessesView.php';

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
