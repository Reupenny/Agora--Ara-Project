<?php
/*
    Orders Controller
    Handles user's order history listing
*/

include_once 'models/user.php';
include_once 'models/orderManager.php';
include_once 'views/orders.php';

class OrdersController extends AbstractController
{
    protected function getView($isPostback)
    {
        $user = $this->getContext()->getUser();
        
        // Must be logged in as buyer
        if (!$user || !$user->isLoggedIn()) {
            $this->redirectTo('login', 'Please login to view your orders.');
            return null;
        }
        
        // Initialize order manager
        $orderManager = new OrderManager($this->getDB());
        
        // Get user's orders (exclude cart)
        $orders = $orderManager->getUserOrders($user->getUsername(), true);
        
        // Create view
        $view = new OrdersView();
        $view->setOrders($orders);
        $view->setUser($user);
        
        return $view;
    }
}
?>
