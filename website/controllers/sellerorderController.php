<?php
/*
 * Seller Order Controller
 * This controller allows sellers to view and update the status of an order.
 */

include 'models/SellerOrderModel.php';
include 'views/SellerOrderView.php';

class SellerOrderController extends AbstractController {
	
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
		
		// Get order ID from URI
		$orderId = $this->getURI()->getID();
		
		if ($orderId === null) {
			throw new InvalidRequestException('Order ID is required');
		}
		
		// Handle status update
		if ($isPostback && isset($_POST['action']) && $_POST['action'] === 'update-status') {
			return $this->handleStatusUpdate($user, $orderId);
		}
		
		// Load order
		$model = new SellerOrderModel($this->getDB());
		
		try {
			$model->load($orderId, $user->getUsername());
		} catch (Exception $e) {
			$this->redirectTo('seller-orders', $e->getMessage());
			return null;
		}
		
		// Check if seller can manage this order
		if (!$model->canManage()) {
			$this->redirectTo('seller-orders', 'You do not have permission to manage this order.');
			return null;
		}
		
		// Create view
		$view = new SellerOrderView();
		$view->setOrder($model->getOrder());
		$view->setOrderItems($model->getOrderItems());
		$view->setOrderTotal($model->getOrderTotal());
		$view->setCanManage($model->canManage());
		
		// Check for success message from redirect
		if (isset($_GET['message'])) {
			$view->setSuccessMessage($_GET['message']);
		}
		
		return $view;
	}
	
	private function handleStatusUpdate($user, $orderId)
	{
		try {
			$newStatus = $_POST['status'] ?? '';
			
			if (empty($newStatus)) {
				throw new Exception('Status is required.');
			}
			
			// Update status
			$model = new SellerOrderModel($this->getDB());
			$model->updateOrderStatus($orderId, $newStatus, $user->getUsername());
			
			// Redirect with success
			$this->redirectTo('seller-order/' . $orderId, 'Order status updated successfully!');
			return null;
			
		} catch (Exception $e) {
			// Reload page with error
			$model = new SellerOrderModel($this->getDB());
			$model->load($orderId, $user->getUsername());
			
			$view = new SellerOrderView();
			$view->setOrder($model->getOrder());
			$view->setOrderItems($model->getOrderItems());
			$view->setOrderTotal($model->getOrderTotal());
			$view->setCanManage($model->canManage());
			$view->setErrorMessage($e->getMessage());
			
			return $view;
		}
	}
}
?>
