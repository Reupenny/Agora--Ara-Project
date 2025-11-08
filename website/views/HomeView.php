<?php
/*
 * Home View
 * This view is responsible for rendering the home page with featured products and businesses.
 */

class HomeView extends AbstractView {
	
	public function prepare() {
		$model = $this->getModel();
		
		// Set page title
		$this->setTemplateField('pagename', 'Home - Agora');
		
		// Load the home page template
		$content = file_get_contents('html/home.html');
		
		// Generate featured products HTML using product card template
		$productsHtml = $this->renderFeaturedProducts($model->getFeaturedProducts());
		$content = str_replace('##featured_products##', $productsHtml, $content);
		
		// Generate featured businesses HTML using business card template
		$businessesHtml = $this->renderFeaturedBusinesses($model->getFeaturedBusinesses());
		$content = str_replace('##featured_businesses##', $businessesHtml, $content);
		
		$this->setTemplateField('content', $content);
		
		// Set the master template
		$this->setTemplate('html/masterPage.html');
	}
	
	// Render featured products using product card template
	private function renderFeaturedProducts($products) {
		if (empty($products)) {
			return '<div class="empty-state"><p>No featured products available at this time.</p></div>';
		}
		
		$cardTemplate = file_get_contents('html/sections/product_card.html');
		$html = '';
		
		foreach ($products as $product) {
			$cardHtml = $cardTemplate;
			
			// Get image URLs or use defaults
			$blurImage = '##site##assets/images/tile.webp';
			$thumbImage = '##site##assets/images/tile.webp';
			
			if (!empty($product['images']) && isset($product['images'][0])) {
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
			
			// Replace tokens
			$cardHtml = str_replace('##product_url##', $product['id'], $cardHtml);
			$cardHtml = str_replace('##product_name##', htmlspecialchars($product['name']), $cardHtml);
			$cardHtml = str_replace('##product_business##', htmlspecialchars($product['businessName']), $cardHtml);
			$cardHtml = str_replace('##product_price##', htmlspecialchars($product['formattedPrice']), $cardHtml);
			$cardHtml = str_replace('##availability_class##', $availabilityClass, $cardHtml);
			$cardHtml = str_replace('##product_availability##', htmlspecialchars($availabilityText), $cardHtml);
			$cardHtml = str_replace('##product_categories##', '', $cardHtml);
			$cardHtml = str_replace('##blur_image##', $blurImage, $cardHtml);
			$cardHtml = str_replace('##thumb_image##', $thumbImage, $cardHtml);
			
			$html .= $cardHtml . "\n";
		}
		
		return $html;
	}
	
	// Render featured businesses using business card template
	private function renderFeaturedBusinesses($businesses) {
		if (empty($businesses)) {
			return '<div class="empty-state"><p>No featured businesses available at this time.</p></div>';
		}
		
		$cardTemplate = file_get_contents('html/sections/business-card.html');
		$html = '';
		
		foreach ($businesses as $business) {
			$cardHtml = $cardTemplate;
			
			// Replace tokens
			$cardHtml = str_replace('##business_url##', $business['id'], $cardHtml);
			$cardHtml = str_replace('##business_name##', htmlspecialchars($business['name']), $cardHtml);
			$cardHtml = str_replace('##business_location##', htmlspecialchars($business['location'] ?? ''), $cardHtml);
			$cardHtml = str_replace('##business_description##', htmlspecialchars($business['description'] ?? 'Visit this store to see their products.'), $cardHtml);
			
			
			$html .= $cardHtml . "\n";
		}
		
		return $html;
	}
}
?>
