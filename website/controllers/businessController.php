<?php
/*
	Business Controller
	Handles business detail pages
*/

include 'models/business.php';
include 'views/business.php';

class BusinessController extends AbstractController {
	
	protected function getView($isPostback) {
		// Get the business ID from URI
		$businessId = $this->getURI()->getID();
		
		if ($businessId === null) {
			throw new InvalidRequestException('Business ID is required');
		}
		
		// Create model and load business data
		$model = new BusinessModel($this->getDB());
		$model->load($businessId);
		
		// Create view
		$view = new BusinessView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
