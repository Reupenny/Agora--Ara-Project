<?php
/*
 * Admin Panel Controller
 * Handles administration functions, such as business approvals.
 */

include_once 'models/UserModel.php';
include_once 'models/BusinessManagerModel.php';
include_once 'views/AdminPanelView.php';

class AdminPanelController extends AbstractController
{
    private $businessManager;
    
    protected function getView($isPostback)
    {
        $user = $this->getContext()->getUser();
        
        // Must be logged in as admin
        if (!$user || !$user->isLoggedIn() || !$user->isAdmin()) {
            $this->redirectTo('home', 'Access denied. Admin privileges required.');
            return null;
        }
        
        // Initialise business manager
        $this->businessManager = new BusinessManagerModel($this->getDB());
        
        // If POST request, handle actions
        if ($isPostback) {
            return $this->handleAction($user);
        }
        
        // Create view
        $view = new AdminPanelView();
        $view->setBusinessManager($this->businessManager);
        
        return $view;
    }
    
    private function handleAction($user)
    {
        try {
            $action = $_POST['action'] ?? '';
            $businessId = intval($_POST['business_id'] ?? 0);
            
            if (!$businessId) {
                throw new Exception('Invalid business ID.');
            }
            
            switch ($action) {
                case 'approve':
                    $this->businessManager->approveBusiness($businessId);
                    $message = 'Business approved successfully!';
                    break;
                    
                case 'deactivate':
                    $this->businessManager->deactivateBusiness($businessId);
                    $message = 'Business deactivated.';
                    break;
                    
                default:
                    throw new Exception('Invalid action.');
            }
            
            // Redirect back with success message
            $this->redirectTo('admin-panel', $message);
            return null;
            
        } catch (Exception $e) {
            // On error, show the view with error message
            $view = new AdminPanelView();
            $view->setBusinessManager($this->businessManager);
            $view->setErrorMessage($e->getMessage());
            return $view;
        }
    }
}
