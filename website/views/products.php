<?php
/*
	Products View
	Displays product listing/shop page
*/

class ProductsView extends AbstractView {
	
	public function prepare() {
		$model = $this->getModel();
		
		// Set page title
		$pageTitle = 'Shop';
		if ($model->getCategoryFilter() !== null) {
			$pageTitle = ucfirst($model->getCategoryFilter()) . ' Products';
		} elseif ($model->getSearchQuery() !== null) {
			$pageTitle = 'Search Results: ' . htmlspecialchars($model->getSearchQuery());
		}
		$this->setTemplateField('pagename', $pageTitle . ' - Agora');
		
		// For now, create a simple product listing page
		$content = '<div class="products-section">';
		$content .= '<h1>' . htmlspecialchars($pageTitle) . '</h1>';
		
		if ($model->getProductCount() === 0) {
			$content .= '<p>No products found.</p>';
		} else {
			$content .= '<div class="products-grid">';
			foreach ($model->getProducts() as $product) {
				$content .= $this->renderProductCard($product);
			}
			$content .= '</div>';
		}
		
		$content .= '</div>';
		
		$this->setTemplateField('content', $content);
		
		// Set the master template
		$this->setTemplate('html/masterPage.html');
	}
	
	private function renderProductCard($product) {
		// Load the product card template
		$cardTemplate = file_get_contents('html/sections/product_card.html');
		
		// Get first image or use default
		$imageUrl = 'assets/images/tile.webp';
		if (isset($product['images']) && count($product['images']) > 0) {
			$imageUrl = $product['images'][0]['url'];
		}
		
		// Determine availability
		$availabilityClass = ($product['isActive'] === 'True') ? 'available' : 'unavailable';
		$availabilityText = ($product['isActive'] === 'True') ? 'Available' : 'Out of Stock';
		
		// Replace tokens in template
		$cardHtml = str_replace('##product_url##', $product['id'], $cardTemplate);
		$cardHtml = str_replace('##product_name##', htmlspecialchars($product['name']), $cardHtml);
		$cardHtml = str_replace('##product_business##', htmlspecialchars($product['businessName']), $cardHtml);
		$cardHtml = str_replace('##product_price##', htmlspecialchars($product['formattedPrice']), $cardHtml);
		$cardHtml = str_replace('##availability_class##', $availabilityClass, $cardHtml);
		$cardHtml = str_replace('##product_availability##', htmlspecialchars($availabilityText), $cardHtml);
		$cardHtml = str_replace('##product_categories##', '', $cardHtml); // Categories can be added later
		
		// Replace the image separately to handle the full path
		$cardHtml = str_replace('##site##assets/images/products/##product_url##/feature.webp', '##site##' . $imageUrl, $cardHtml);
		
		return $cardHtml;
	}
}
?>
