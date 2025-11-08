<?php
/*
 * Products Controller
 * This controller is responsible for handling the product listing and shop pages.
 */

include 'models/ProductsModel.php';
include 'views/ProductsView.php';

class ProductsController extends AbstractController {
	
	protected function getView($isPostback) {
		// Get current user
		$user = $this->getContext()->getUser();
		
		// Create model
		$model = new ProductsModel($this->getDB());
		
		// Set username if user is logged in and is a seller
		if ($user && $user->isLoggedIn() && $user->isSeller()) {
			$model->setUsername($user->getUsername());
		}
		
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
