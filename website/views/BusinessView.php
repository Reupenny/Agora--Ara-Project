<?php
/*
	Business View
	Displays business detail page
*/

class BusinessView extends AbstractView {
	
	private $canEdit = false;
	
	public function setCanEdit($canEdit)
	{
		$this->canEdit = $canEdit;
	}
	
	public function prepare() {
		$model = $this->getModel();
		
		// Set page title
		$this->setTemplateField('pagename', $model->getBusinessName() . ' - Business');
		
		// Load the business detail template
		$content = file_get_contents('html/business-detail.html');
		
		// Add edit button for administrators at the top
		if ($this->canEdit) {
			$editButton = '<div style="margin-bottom: 20px; text-align: right;">
				<a href="##site##business-manage" class="btn-primary">Edit Business</a>
			</div>';
			$content = $editButton . $content;
		}
		
		// Replace business information tokens
		$content = str_replace('##business_url##', $model->getBusinessId(), $content);
		$content = str_replace('##business_name##', htmlspecialchars($model->getBusinessName()), $content);
		
		// Business description
		$description = $model->getDetails() 
			? nl2br(htmlspecialchars($model->getDetails()))
			: '<p>No description available for this business.</p>';
		$content = str_replace('##business_description##', $description, $content);
		
		// Business location
		$location = $model->getLocation() 
			? nl2br(htmlspecialchars($model->getLocation()))
			: 'Location not available';
		$content = str_replace('##business_location##', $location, $content);
		
		// Business contact info (placeholders for now - these fields don't exist in the database yet)
		$content = str_replace('##business_email##',strtolower($model->getBusinessEmail()), $content);
		$content = str_replace('##business_phone##', strtolower($model->getBusinessPhone()), $content);
		
		// Generate product cards dynamically using template
		$products = $model->getProducts();
		$productsHtml = '';
		
		if (count($products) > 0) {
			// Load the product card template once
			$cardTemplate = file_get_contents('html/sections/product_card.html');
			
			foreach ($products as $product) {
				// Get image URLs or use defaults
				$blurImage = '##site##assets/images/tile.webp';
				$thumbImage = '##site##assets/images/tile.webp';
				
				if (isset($product['images']) && count($product['images']) > 0) {
					if (!empty($product['images'][0]['blur'])) {
						$blurImage = '##site##' . $product['images'][0]['blur'];
					}
					if (!empty($product['images'][0]['thumb'])) {
						$thumbImage = '##site##' . $product['images'][0]['thumb'];
					}
				}
				
				// Determine availability
				$availabilityClass = ($product['stockQuantity'] > 0) ? 'available' : 'unavailable';
			$availabilityText = ($product['stockQuantity'] > 0)  ? 'Available' : 'Out of Stock';
				
				// Replace tokens in template for each product
				$cardHtml = str_replace('##product_url##', $product['id'], $cardTemplate);
				$cardHtml = str_replace('##product_name##', htmlspecialchars($product['name']), $cardHtml);
				$cardHtml = str_replace('##product_business##', htmlspecialchars($model->getBusinessName()), $cardHtml);
				$cardHtml = str_replace('##product_price##', htmlspecialchars($product['formattedPrice']), $cardHtml);
				$cardHtml = str_replace('##availability_class##', $availabilityClass, $cardHtml);
				$cardHtml = str_replace('##product_availability##', htmlspecialchars($availabilityText), $cardHtml);
				$cardHtml = str_replace('##product_categories##', '', $cardHtml); // Categories can be added later
				$cardHtml = str_replace('##blur_image##', $blurImage, $cardHtml);
				$cardHtml = str_replace('##thumb_image##', $thumbImage, $cardHtml);
				
				$productsHtml .= $cardHtml . "\n";
			}
		} else {
			$productsHtml = '<p>This business currently has no products listed.</p>';
		}
		
		// Replace the products token
		$content = str_replace('##business_products##', $productsHtml, $content);
		
		$this->setTemplateField('content', $content);
		
		// Set the master template
		$this->setTemplate('html/masterPage.html');
	}
}
?>
