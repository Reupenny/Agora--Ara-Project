<?php
/*
	Home Controller
	Handles the home page
*/

include 'models/home.php';
include 'views/home.php';

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
