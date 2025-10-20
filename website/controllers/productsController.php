<?php
/*
	Products Controller
	Handles product listing/shop pages
*/

include 'models/products.php';
include 'views/products.php';

class ProductsController extends AbstractController {
	
	protected function getView($isPostback) {
		// Create model
		$model = new ProductsModel($this->getDB());
		
		// Check for category filter from query string
		if (isset($_GET['category'])) {
			$model->setCategoryFilter($_GET['category']);
		}
		
		// Check for search query
		if (isset($_GET['search'])) {
			$model->setSearchQuery($_GET['search']);
		}
		
		// Load products
		$model->loadProducts();
		
		// Create view
		$view = new ProductsView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
