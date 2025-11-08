<?php
/*
	Business Controller
	Handles business detail pages
*/

include 'models/BusinessModel.php';
include 'views/BusinessView.php';

class BusinessController extends AbstractController {
	
	protected function getView($isPostback) {
		// Get the business ID from URI
		$businessId = $this->getURI()->getID();
		
		if ($businessId === null) {
			throw new InvalidRequestException('Business ID is required');
		}
		
		// Create model and load business data
		$model = new BusinessModel($this->getDB());
		$model->load($businessId);
		
		// Only allow viewing approved businesses (unless user is admin or business owner)
		$user = $this->getContext()->getUser();
		$isOwner = false;
		$canEdit = false;
		
		if ($user && $user->isLoggedIn()) {
			// Check if user is associated with this business
			$sql = "SELECT role_name FROM business_association 
			        WHERE business_id = ? AND username = ? AND is_active = 'True'";
			$result = $this->getDB()->queryPrepared($sql, [$businessId, $user->getUsername()]);
			
			if (!empty($result)) {
				$isOwner = true;
				// Only administrators can edit the business
				$canEdit = ($result[0]['role_name'] === 'Administrator');
			}
		}
		
		// If business is not active and user is not admin or owner, deny access
		if ($model->getIsActive() !== 'True' && 
		    (!$user || !$user->isAdmin()) && 
		    !$isOwner) {
			throw new InvalidRequestException('This business is not available for viewing.');
		}
		
		// Create view
		$view = new BusinessView();
		$view->setModel($model);
		$view->setCanEdit($canEdit);
		
		return $view;
	}
}
?>
