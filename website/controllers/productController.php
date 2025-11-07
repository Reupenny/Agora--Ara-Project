<?php
/*
	Product Controller
	Handles individual product detail pages
*/

include 'models/product.php';
include 'models/orderManager.php';
include 'views/product.php';

class ProductController extends AbstractController {
	
	protected function getView($isPostback) {
		$user = $this->getContext()->getUser();
		
		// Get the product ID from URI
		$productId = $this->getURI()->getID();
		
		if ($productId === null) {
			throw new InvalidRequestException('Product ID is required');
		}
		
		// Handle add to cart action
		if ($isPostback && isset($_POST['action']) && $_POST['action'] === 'add-to-cart') {
			return $this->handleAddToCart($user, $productId);
		}
		
		// Create model and load product data
		$model = new ProductModel($this->getDB());
		$model->load($productId);
		
		// Check if user can edit this product (only sellers of the business)
		$canEdit = false;
		if ($user && $user->isLoggedIn() && $user->isSeller()) {
			$businessId = $model->getBusinessId();
			$username = $user->getUsername();
			
			// Check if user is a seller for this business (not just an administrator)
			$query = "SELECT role_name FROM business_association WHERE username = ? AND business_id = ? AND is_active = 'True' AND role_name = 'Seller'";
			$result = $this->getDB()->queryPrepared($query, [$username, $businessId]);
			
			if (!empty($result)) {
				$canEdit = true;
			}
		}
		
		// Create view
		$view = new ProductView();
		$view->setModel($model);
		$view->setUser($user);
		$view->setCanEdit($canEdit);
		
		return $view;
	}
	
	private function handleAddToCart($user, $productId)
	{
		try {
			// Must be logged in
			if (!$user || !$user->isLoggedIn()) {
				$this->redirectTo('login', 'Please login to add items to your cart.');
				return null;
			}
			
			// Only buyers can purchase products
			if ($user->getAccountType() !== 'Buyer') {
				throw new Exception('Only buyers can add items to cart. Sellers and administrators cannot purchase products.');
			}
			
			// Get quantity from form
			$quantity = intval($_POST['quantity'] ?? 1);
			
			if ($quantity < 1) {
				throw new Exception('Invalid quantity.');
			}
			
			// Add to cart
			$orderManager = new OrderManager($this->getDB());
			$orderManager->addToCart($user->getUsername(), $productId, $quantity);
			
			// Redirect to cart
			$this->redirectTo('cart', 'Product added to cart!');
			return null;
			
		} catch (Exception $e) {
			// Show product page with error
			$model = new ProductModel($this->getDB());
			$model->load($productId);
			
			$view = new ProductView();
			$view->setModel($model);
			$view->setUser($user);
			$view->setErrorMessage($e->getMessage());
			
			return $view;
		}
	}
}
?>
