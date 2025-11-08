<?php
/*
 * Home Controller
 * This controller is responsible for handling the home page.
 */

include 'models/HomeModel.php';
include 'views/HomeView.php';

class HomeController extends AbstractController {
	
	protected function getView($isPostback) {
		// Create model
		$model = new HomeModel($this->getDB());
		
		// Load featured products (limit to 4)
		$model->loadFeaturedProducts(4);
		
		// Load featured businesses (limit to 4)
		$model->loadFeaturedBusinesses(4);
		
		// Create view
		$view = new HomeView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
