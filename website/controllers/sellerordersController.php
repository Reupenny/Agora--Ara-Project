<?php
/*
	Seller Orders Controller
	Shows all orders containing products from seller's businesses
*/

include 'models/sellerOrders.php';
include 'views/sellerOrders.php';

class SellerOrdersController extends AbstractController {
	
	protected function getView($isPostback) {
		$user = $this->getContext()->getUser();
		
		// Must be logged in
		if (!$user || !$user->isLoggedIn()) {
			$this->redirectTo('login', 'Please login to view orders.');
			return null;
		}
		
		// Must be a seller
		if ($user->getAccountType() !== 'Seller') {
			throw new InvalidRequestException('Only sellers can access this page.');
		}
		
		// Load orders
		$model = new SellerOrdersModel($this->getDB());
		$model->load($user->getUsername());
		
		// Create view
		$view = new SellerOrdersView();
		$view->setModel($model);
		
		return $view;
	}
}
?>
