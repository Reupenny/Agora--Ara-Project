<?php
/*
 * Orders Controller
 * This controller is responsible for handling the listing of a user's order history.
 */

include_once 'models/UserModel.php';
include_once 'models/OrderManagerModel.php';
include_once 'views/OrdersView.php';

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
        
        // Initialise order manager
        $orderManager = new OrderManagerModel($this->getDB());
        
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
