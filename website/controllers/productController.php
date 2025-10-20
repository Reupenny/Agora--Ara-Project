<?php
/*
	Product Controller
	Handles individual product detail pages
*/

include 'models/product.php';
include 'views/product.php';

class ProductController extends AbstractController {
	
	protected function getView($isPostback) {
		// Get the product ID from URI
		$productId = $this->getURI()->getID();
		
		if ($productId === null) {
			throw new InvalidRequestException('Product ID is required');
		}
		
		// Create model and load product data
		$model = new ProductModel($this->getDB());
		$model->load($productId);
		
		// Create view
		$view = new ProductView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
