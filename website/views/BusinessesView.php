<?php
/*
	Businesses View
	Displays listing of all active businesses
*/

class BusinessesView extends AbstractView {
	
	public function prepare() {
		$model = $this->getModel();
		
		// Set page title
		$this->setTemplateField('pagename', 'Browse Businesses - Agora');
		
		// Start building content
		$content = '<h1>Browse Businesses</h1>';
		
		$businesses = $model->getBusinesses();
		$businessCount = $model->getBusinessCount();
		
		if ($businessCount > 0) {
			$content .= '<p style="margin-bottom: 30px; color: #666;">' . $businessCount . ' business' . ($businessCount != 1 ? 'es' : '') . ' available</p>';
			
			// Load the business card template once
			$cardTemplate = file_get_contents('html/sections/business-card.html');
			
			$content .= '<div class="businesses-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px;">';
			
			foreach ($businesses as $business) {
				$businessId = $business['business_id'];
				$businessName = htmlspecialchars($business['business_name']);
				$location = htmlspecialchars($business['business_location']);
				$description = htmlspecialchars($business['short_description'] ?? '');
				
				// If no short description, use truncated details
				if (empty($description) && !empty($business['details'])) {
					$details = strip_tags($business['details']);
					$description = strlen($details) > 100 ? substr($details, 0, 100) . '...' : $details;
				}
				
				// Replace tokens in template
				$cardHtml = str_replace('##site##', '##site##', $cardTemplate);
				$cardHtml = str_replace('##business_url##', $businessId, $cardHtml);
				$cardHtml = str_replace('##business_name##', $businessName, $cardHtml);
				$cardHtml = str_replace('##business_location##', $location, $cardHtml);
				$cardHtml = str_replace('##business_description##', $description, $cardHtml);
				
				$content .= $cardHtml;
			}
			
			$content .= '</div>';
		} else {
			$content .= '<p style="text-align: center; padding: 40px; color: #666;">No businesses are currently listed.</p>';
		}
		
		$this->setTemplateField('content', $content);
		$this->setTemplate('html/masterPage.html');
	}
}
?>
