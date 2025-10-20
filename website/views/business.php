<?php
/*
	Business View
	Displays business detail page
*/

class BusinessView extends AbstractView {
	
	public function prepare() {
		$model = $this->getModel();
		
		// Set page title
		$this->setTemplateField('pagename', $model->getBusinessName() . ' - Agora');
		
		// Load the business detail template
		$content = file_get_contents('html/business-detail.html');
		
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
		$content = str_replace('##business_email##', 'contact@' . strtolower(str_replace(' ', '', $model->getBusinessName())) . '.co.nz', $content);
		$content = str_replace('##business_phone##', '+64 3 123 4567', $content);
		
		// Generate product cards dynamically using template
		$products = $model->getProducts();
		$productsHtml = '';
		
		if (count($products) > 0) {
			// Load the product card template once
			$cardTemplate = file_get_contents('html/sections/product_card.html');
			
			foreach ($products as $product) {
				// Get first image or use default
				$imageUrl = 'assets/images/tile.webp';
				if (isset($product['images']) && count($product['images']) > 0) {
					$imageUrl = $product['images'][0]['url'];
				}
				
				// Determine availability
				$availabilityClass = ($product['isActive'] === 'True') ? 'available' : 'unavailable';
				$availabilityText = ($product['isActive'] === 'True') ? 'Available' : 'Out of Stock';
				
				// Replace tokens in template for each product
				$cardHtml = str_replace('##product_url##', $product['id'], $cardTemplate);
				$cardHtml = str_replace('##product_name##', htmlspecialchars($product['name']), $cardHtml);
				$cardHtml = str_replace('##product_business##', htmlspecialchars($model->getBusinessName()), $cardHtml);
				$cardHtml = str_replace('##product_price##', htmlspecialchars($product['formattedPrice']), $cardHtml);
				$cardHtml = str_replace('##availability_class##', $availabilityClass, $cardHtml);
				$cardHtml = str_replace('##product_availability##', htmlspecialchars($availabilityText), $cardHtml);
				$cardHtml = str_replace('##product_categories##', '', $cardHtml); // Categories can be added later
				
				// Replace the image path
				$cardHtml = str_replace('##site##assets/images/products/##product_url##/feature.webp', '##site##' . $imageUrl, $cardHtml);
				
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
