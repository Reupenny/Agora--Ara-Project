<?php
/*
 * Products View
 * This view is responsible for displaying the product listing/shop page.
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
		
		// Replace tokens in template
		$cardHtml = str_replace('##product_url##', $product['id'], $cardTemplate);
		$cardHtml = str_replace('##product_name##', htmlspecialchars($product['name']), $cardHtml);
		$cardHtml = str_replace('##product_business##', htmlspecialchars($product['businessName']), $cardHtml);
		$cardHtml = str_replace('##product_price##', htmlspecialchars($product['formattedPrice']), $cardHtml);
		$cardHtml = str_replace('##availability_class##', $availabilityClass, $cardHtml);
		$cardHtml = str_replace('##product_availability##', htmlspecialchars($availabilityText), $cardHtml);
		$cardHtml = str_replace('##product_categories##', '', $cardHtml); // Categories can be added later
		$cardHtml = str_replace('##blur_image##', $blurImage, $cardHtml);
		$cardHtml = str_replace('##thumb_image##', $thumbImage, $cardHtml);
		
		return $cardHtml;
	}
}
?>
