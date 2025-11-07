<?php
/*
    Cart Controller
    Handles shopping cart operations
*/

include_once 'models/user.php';
include_once 'models/orderManager.php';
include_once 'views/cart.php';

class CartController extends AbstractController
{
    private $orderManager;
    
    protected function getView($isPostback)
    {
        $user = $this->getContext()->getUser();
        
        // Must be logged in
        if (!$user || !$user->isLoggedIn()) {
            $this->redirectTo('login', 'Please login to view your cart.');
            return null;
        }
        
        // Initialize order manager
        $this->orderManager = new OrderManager($this->getDB());
        
        // Handle POST actions
        if ($isPostback) {
            return $this->handleAction($user);
        }
        
        // Get cart
        $cart = $this->orderManager->getCart($user->getUsername());
        $cartItems = $this->orderManager->getCartItems($cart['order_id']);
        $cartTotal = $this->orderManager->calculateCartTotal($cart['order_id']);
        
        // Create view
        $view = new CartView();
        $view->setCart($cart);
        $view->setCartItems($cartItems);
        $view->setCartTotal($cartTotal);
        
        return $view;
    }
    
    private function handleAction($user)
    {
        try {
            $action = $_POST['action'] ?? '';
            $cart = $this->orderManager->getCart($user->getUsername());
            $orderId = $cart['order_id'];
            
            switch ($action) {
                case 'update':
                    // Update item quantity
                    $productId = intval($_POST['product_id'] ?? 0);
                    $quantity = intval($_POST['quantity'] ?? 1);
                    
                    if ($productId && $quantity >= 0) {
                        $this->orderManager->updateCartItem($orderId, $productId, $quantity);
                        $message = $quantity > 0 ? 'Cart updated.' : 'Item removed from cart.';
                        $this->redirectTo('cart', $message);
                    }
                    break;
                    
                case 'remove':
                    // Remove item
                    $productId = intval($_POST['product_id'] ?? 0);
                    
                    if ($productId) {
                        $this->orderManager->removeCartItem($orderId, $productId);
                        $this->redirectTo('cart', 'Item removed from cart.');
                    }
                    break;
                    
                case 'checkout':
                    // Process checkout
                    $this->orderManager->checkout($orderId);
                    $this->redirectTo('orders', 'Order placed successfully! Your order is pending and can be edited if needed.');
                    return null;
                    
                default:
                    throw new Exception('Invalid action.');
            }
            
            return null;
            
        } catch (Exception $e) {
            // Show cart with error
            $cart = $this->orderManager->getCart($user->getUsername());
            $cartItems = $this->orderManager->getCartItems($cart['order_id']);
            $cartTotal = $this->orderManager->calculateCartTotal($cart['order_id']);
            
            $view = new CartView();
            $view->setCart($cart);
            $view->setCartItems($cartItems);
            $view->setCartTotal($cartTotal);
            $view->setErrorMessage($e->getMessage());
            
            return $view;
        }
    }
}
?>
