<?php
/*
	Product View
	Displays individual product detail page
*/

class ProductView extends AbstractView {
	
	public function prepare() {
		$model = $this->getModel();
		
		// Set page title
		$this->setTemplateField('pagename', $model->getName() . ' - Agora');
		
		// Load the product detail template
		$content = file_get_contents('html/product-detail.html');
		
		// Replace product information tokens
		$content = str_replace('##product_name##', htmlspecialchars($model->getName()), $content);
		$content = str_replace('##product_price##', htmlspecialchars($model->getFormattedPrice()), $content);
		
		// Product description
		$description = $model->getDescription() ? nl2br(htmlspecialchars($model->getDescription())) : '<p>No description available.</p>';
		$content = str_replace('##product_description##', $description, $content);
		
		// Seller information using business card template
		$sellerInfo = 'Sold by <a href="##site##business/' . $model->getBusinessId() . '" class="seller-link">' 
		            . htmlspecialchars($model->getBusinessName()) . '</a>';
		$content = str_replace('##seller_info##', $sellerInfo, $content);
		
		// Load business card template for seller details
		$businessCardTemplate = file_get_contents('html/sections/business-card.html');
		$businessCard = str_replace('##business_url##', $model->getBusinessId(), $businessCardTemplate);
		$businessCard = str_replace('##business_name##', htmlspecialchars($model->getBusinessName()), $businessCard);
		$businessLocation = htmlspecialchars($model->getBusinessLocation());
		$businessDescription = $model->getBusinessDescription() ? nl2br(htmlspecialchars($model->getBusinessDescription())) : 'Visit this seller\'s store to see more products.';
		$businessCard = str_replace('##business_location##', $businessLocation, $businessCard);
		$businessCard = str_replace('##business_description##', $businessDescription, $businessCard);
		
		$content = str_replace('##seller_details##', $businessCard, $content);
		
		// Stock status
		$stockStatus = $model->isInStock() 
			? 'In Stock: <strong>' . $model->getStockQuantity() . ' available</strong>'
			: '<strong class="out-of-stock">Out of Stock</strong>';
		$content = str_replace('##stock_status##', $stockStatus, $content);
		
		// Availability badge
		$availabilityClass = $model->isInStock() ? 'available' : 'unavailable';
		$availabilityText = $model->isInStock() ? 'Available' : 'Out of Stock';
		$content = str_replace('##availability_class##', $availabilityClass, $content);
		$content = str_replace('##availability_text##', $availabilityText, $content);
		
		// Max quantity in form
		$content = str_replace('##max_quantity##', $model->getStockQuantity(), $content);
		
		// Generate product images gallery (do this before replacing availability tokens)
		$images = $model->getImages();
		$imagesHtml = $this->renderProductImages($images, $model->getProductId(), $availabilityClass, $availabilityText);
		$content = str_replace('##product_images##', $imagesHtml, $content);
		
		// Generate categories HTML
		$categories = $model->getCategories();
		if (count($categories) > 0) {
			$categoriesHtml = '';
			foreach ($categories as $category) {
				$categoriesHtml .= '<span><a class="category" href="##site##shop?category=' . urlencode($category) . '">' 
				          . htmlspecialchars($category) . '</a></span>' . "\n            ";
			}
			$content = str_replace('##product_categories##', rtrim($categoriesHtml), $content);
		} else {
			$content = str_replace('##product_categories##', '<span class="category">No categories</span>', $content);
		}
		
		// Related products section - placeholder for now (could be enhanced to show actual related products)
		// For now, just show empty or a message
		$relatedProducts = $model->getRelatedProducts(3);
		$relatedProductsHtml = $this->renderRelatedProducts($relatedProducts);
		$content = str_replace('##related_products##', $relatedProductsHtml, $content);
		
		$this->setTemplateField('content', $content);
		
		// Set the master template
		$this->setTemplate('html/masterPage.html');
	}
	
	// Render related products using product card template
	private function renderRelatedProducts($products) {
		if (empty($products)) {
			return '<p class="empty-state">No related products found.</p>';
		}
		
		$cardTemplate = file_get_contents('html/sections/product_card.html');
		$html = '';
		
		foreach ($products as $product) {
			$cardHtml = $cardTemplate;
			
			// Determine image
			$imagePath = 'assets/images/tile.webp';
			if (!empty($product['images']) && isset($product['images'][0]['url'])) {
				$imagePath = $product['images'][0]['url'];
			}
			
			// Determine availability
			$availabilityClass = ($product['isActive'] === 'True') ? 'available' : 'unavailable';
			$availabilityText = ($product['isActive'] === 'True') ? 'Available' : 'Out of Stock';
			
			// Replace tokens
			$cardHtml = str_replace('##product_url##', $product['id'], $cardHtml);
			$cardHtml = str_replace('##product_name##', htmlspecialchars($product['name']), $cardHtml);
			$cardHtml = str_replace('##product_business##', htmlspecialchars($product['businessName']), $cardHtml);
			$cardHtml = str_replace('##product_price##', htmlspecialchars($product['formattedPrice']), $cardHtml);
			$cardHtml = str_replace('##availability_class##', $availabilityClass, $cardHtml);
			$cardHtml = str_replace('##product_availability##', htmlspecialchars($availabilityText), $cardHtml);
			$cardHtml = str_replace('##product_categories##', '', $cardHtml); // No categories in related products
			
			// Replace image path
			$cardHtml = str_replace('##site##assets/images/products/##product_url##/feature.webp', '##site##' . $imagePath, $cardHtml);
			
			$html .= $cardHtml . "\n";
		}
		
		return $html;
	}
	
	// Render product images gallery (main image + thumbnails)
	private function renderProductImages($images, $productId, $availabilityClass, $availabilityText) {
		$defaultImage = '##site##assets/images/tile.webp';
		
		// If no images, use default
		if (empty($images)) {
			$mainImage = $defaultImage;
			$thumbnails = '<img src="' . $defaultImage . '" alt="Product Image" class="thumbnail active" onclick="changeImage(this)">';
		} else {
			// First image as main image
			$mainImage = '##site##' . htmlspecialchars($images[0]['url']);
			
			// Generate thumbnails for all images
			$thumbnails = '';
			foreach ($images as $index => $image) {
				$activeClass = ($index === 0) ? ' active' : '';
				$imageUrl = '##site##' . htmlspecialchars($image['thumb'] ?? $image['url']);
				$thumbnails .= '<img src="' . $imageUrl . '" alt="Thumbnail ' . ($index + 1) . '" class="thumbnail' . $activeClass . '" onclick="changeImage(this)">' . "\n            ";
			}
			$thumbnails = rtrim($thumbnails);
		}
		
		// Build the gallery HTML
		$html = '<div class="main-image">' . "\n";
		$html .= '            <img src="' . $mainImage . '" alt="Product Main Image" id="main-product-image">' . "\n";
		$html .= '            <span class="product-status-badge ' . $availabilityClass . '">' . htmlspecialchars($availabilityText) . '</span>' . "\n";
		$html .= '        </div>' . "\n\n";
		$html .= '        <div class="thumbnail-gallery">' . "\n";
		$html .= '            ' . $thumbnails . "\n";
		$html .= '        </div>';
		
		return $html;
	}
}
?>
