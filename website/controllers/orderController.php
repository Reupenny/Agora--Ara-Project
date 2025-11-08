<?php
/*
 * Order Controller
 * This controller is responsible for handling the viewing and editing of individual orders.
 */

include_once 'models/UserModel.php';
include_once 'models/OrderManagerModel.php';
include_once 'views/OrderView.php';

class OrderController extends AbstractController
{
    private $orderManager;
    
    protected function getView($isPostback)
    {
        $user = $this->getContext()->getUser();
        
        // Must be logged in
        if (!$user || !$user->isLoggedIn()) {
            $this->redirectTo('login', 'Please login to view your order.');
            return null;
        }
        
        // Get order ID from URI
        $orderId = $this->getURI()->getID();
        
        if (!$orderId) {
            throw new InvalidRequestException('Order ID is required');
        }
        
        // Initialise order manager
        $this->orderManager = new OrderManagerModel($this->getDB());
        
        // Get order
        $order = $this->orderManager->getOrder($orderId);
        
        if (!$order) {
            throw new InvalidRequestException('Order not found');
        }
        
        // Verify user owns this order
        if ($order['buyer_username'] !== $user->getUsername()) {
            throw new InvalidRequestException('Access denied');
        }
        
        // Handle POST actions
        if ($isPostback) {
            return $this->handleAction($user, $order);
        }
        
        // Get order items
        $orderItems = $this->orderManager->getOrderItems($orderId);
        $orderTotal = $this->orderManager->calculateCartTotal($orderId);
        
        // Create view
        $view = new OrderView();
        $view->setOrder($order);
        $view->setOrderItems($orderItems);
        $view->setOrderTotal($orderTotal);
        $view->setCanEdit($this->orderManager->canModifyOrder($orderId, $user->getUsername()));
        
        return $view;
    }
    
    private function handleAction($user, $order)
    {
        try {
            $action = $_POST['action'] ?? '';
            $orderId = $order['order_id'];
            
            // Can only modify processing/pending orders
            if (!in_array($order['status'], ['Processing', 'Pending'])) {
                throw new Exception('This order can no longer be modified.');
            }
            
            switch ($action) {
                case 'update':
                    // Update item quantity
                    $productId = intval($_POST['product_id'] ?? 0);
                    $quantity = intval($_POST['quantity'] ?? 1);
                    
                    if ($productId && $quantity >= 0) {
                        $this->orderManager->updateCartItem($orderId, $productId, $quantity);
                        $message = $quantity > 0 ? 'Order updated.' : 'Item removed from order.';
                        $this->redirectTo('order/' . $orderId, $message);
                    }
                    break;
                    
                case 'remove':
                    // Remove item
                    $productId = intval($_POST['product_id'] ?? 0);
                    
                    if ($productId) {
                        $this->orderManager->removeCartItem($orderId, $productId);
                        $this->redirectTo('order/' . $orderId, 'Item removed from order.');
                    }
                    break;
                    
                default:
                    throw new Exception('Invalid action.');
            }
            
            return null;
            
        } catch (Exception $e) {
            // Show order with error
            $orderItems = $this->orderManager->getOrderItems($order['order_id']);
            $orderTotal = $this->orderManager->calculateCartTotal($order['order_id']);
            
            $view = new OrderView();
            $view->setOrder($order);
            $view->setOrderItems($orderItems);
            $view->setOrderTotal($orderTotal);
            $view->setCanEdit($this->orderManager->canModifyOrder($order['order_id'], $user->getUsername()));
            $view->setErrorMessage($e->getMessage());
            
            return $view;
        }
    }
}
?>
