<?php
/*
 * Product View
 * This view is responsible for displaying the individual product detail page.
 */

class ProductView extends AbstractView {
	
	private $user;
	private $errorMessage = '';
	private $canEdit = false;
	
	public function setUser($user)
	{
		$this->user = $user;
	}
	
	public function setErrorMessage($message)
	{
		$this->errorMessage = $message;
	}
	
	public function setCanEdit($canEdit)
	{
		$this->canEdit = $canEdit;
	}
	
	public function prepare() {
		$model = $this->getModel();
		
		// Set page title
		$this->setTemplateField('pagename', $model->getName() . ' - Agora');
		
		// Load the product detail template
		$content = file_get_contents('html/product-detail.html');
		
		// Add edit button for sellers at the top
		if ($this->canEdit) {
			$editButton = '<div style="margin-bottom: 20px; text-align: right;">
				<a href="##site##product-edit/' . $model->getProductId() . '" class="btn-primary" style="text-decoration: none; padding: 10px 20px; display: inline-block;">Edit Product</a>
			</div>';
			$content = $editButton . $content;
		}
		
		// Add error message if present
		if (!empty($this->errorMessage)) {
			$errorHtml = '<div class="status-box status-inactive">
				<strong>Error:</strong> ' . htmlspecialchars($this->errorMessage) . '
			</div>';
			$content = $errorHtml . $content;
		}
		
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
		
		// Generate product images gallery
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
		
		// Related products section
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
			$cardHtml = str_replace('##product_categories##', '', $cardHtml); // No categories in related products
			$cardHtml = str_replace('##blur_image##', $blurImage, $cardHtml);
			$cardHtml = str_replace('##thumb_image##', $thumbImage, $cardHtml);
			
			$html .= $cardHtml . "\n";
		}
		
		return $html;
	}
	
	// Render product images gallery
	private function renderProductImages($images, $productId, $availabilityClass, $availabilityText) {
		$defaultImage = '##site##assets/images/tile.webp';
		
		// If no images, use default
		if (empty($images)) {
			$mainImageBlur = $defaultImage;
			$mainImageFull = $defaultImage;
			$thumbnails = '<img src="' . $defaultImage . '" alt="Product Image" class="thumbnail active" onclick="changeImage(this)" data-full="' . $defaultImage . '">';
		} else {
			// First image
			$mainImageBlur = '##site##' . htmlspecialchars($images[0]['blur'] ?? $images[0]['url']);
			$mainImageFull = '##site##' . htmlspecialchars($images[0]['url']);
			
			// Generate thumbnails for all images
			$thumbnails = '';
			foreach ($images as $index => $image) {
				$activeClass = ($index === 0) ? ' active' : '';
				$thumbUrl = '##site##' . htmlspecialchars($image['thumb'] ?? $image['url']);
				$fullUrl = '##site##' . htmlspecialchars($image['url']);
				$blurUrl = '##site##' . htmlspecialchars($image['blur'] ?? $image['url']);
				$thumbnails .= '<img src="' . $thumbUrl . '" alt="Thumbnail ' . ($index + 1) . '" class="thumbnail' . $activeClass . '" onclick="changeImage(this)" data-full="' . $fullUrl . '" data-blur="' . $blurUrl . '">' . "\n            ";
			}
			$thumbnails = rtrim($thumbnails);
		}
		
		// Build the gallery
		$html = '<div class="main-image">' . "\n";
		$html .= '            <img src="' . $mainImageBlur . '" data-src="' . $mainImageFull . '" alt="Product Main Image" id="main-product-image" class="lazy-load product-main-image">' . "\n";
		$html .= '            <span class="product-status-badge ' . $availabilityClass . '">' . htmlspecialchars($availabilityText) . '</span>' . "\n";
		$html .= '        </div>' . "\n\n";
		$html .= '        <div class="thumbnail-gallery">' . "\n";
		$html .= '            ' . $thumbnails . "\n";
		$html .= '        </div>';
		
		return $html;
	}
}
?>
