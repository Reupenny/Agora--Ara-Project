<?php
/*
	Product Model
	Handles product data from the database
*/

class ProductModel extends AbstractModel {
	
	private $productId;
	private $name;
	private $description;
	private $price;
	private $stockQuantity;
	private $businessId;
	private $businessName;
	private $businessLocation;
	private $businessDescription;
	private $isActive;
	private $createdAt;
	private $categories = [];
	private $images = [];
	
	// Load product from database
	public function load($productId) {
		$this->productId = $productId;
		
		// Query to get product with business info
		$sql = "SELECT p.*, b.business_name, b.business_location, b.details
		        FROM products p
		        INNER JOIN businesses b ON p.business_id = b.business_id
		        WHERE p.product_id = ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$productId]);
		
		if (count($result) === 0) {
			throw new InvalidRequestException('Product not found');
		}
		
		$row = $result[0];
		$this->name = $row['product_name'];
		$this->description = $row['description'];
		$this->price = $row['price'];
		$this->stockQuantity = $row['quantity'];
		$this->businessId = $row['business_id'];
		$this->businessName = $row['business_name'];
		$this->businessLocation = $row['business_location'];
		$this->businessDescription = $row['details'];
		$this->isActive = $row['is_available'];
		$this->createdAt = null; // Not in schema
		
		// Load categories
		$this->loadCategories();
		
		// Load images
		$this->loadImages();
	}
	
	// Load product categories
	private function loadCategories() {
		$sql = "SELECT category_name
		        FROM product_categories
		        WHERE product_id = ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$this->productId]);
		
		$this->categories = [];
		foreach ($result as $row) {
			$this->categories[] = $row['category_name'];
		}
	}
	
	// Load product images
	private function loadImages() {
		$sql = "SELECT image_url, thumb_url, blur_url
		        FROM product_images
		        WHERE product_id = ?
		        ORDER BY sort_order ASC, image_id ASC";
		
		$result = $this->getDB()->queryPrepared($sql, [$this->productId]);
		
		$this->images = [];
		foreach ($result as $row) {
			$this->images[] = [
				'url' => $row['image_url'],
				'thumb' => $row['thumb_url'],
				'blur' => $row['blur_url']
			];
		}
	}
	
	// Getters
	public function getProductId() { return $this->productId; }
	public function getName() { return $this->name; }
	public function getDescription() { return $this->description; }
	public function getPrice() { return $this->price; }
	public function getStockQuantity() { return $this->stockQuantity; }
	public function getBusinessId() { return $this->businessId; }
	public function getBusinessName() { return $this->businessName; }
	public function getBusinessLocation() { return $this->businessLocation; }
	public function getBusinessDescription() { return $this->businessDescription; }
	public function getIsActive() { return $this->isActive; }
	public function getCreatedAt() { return $this->createdAt; }
	public function getCategories() { return $this->categories; }
	public function getImages() { return $this->images; }
	
	public function isInStock() {
		return $this->isActive === 'True' && $this->stockQuantity > 0;
	}
	
	public function getFormattedPrice() {
		return '$' . number_format($this->price, 2);
	}
	
	// Get related products (random 3 products from same category)
	public function getRelatedProducts($limit = 3) {
		// If no categories, return empty array
		if (empty($this->categories)) {
			return [];
		}
		
		// Get the first category to use for finding related products
		$category = $this->categories[0];
		
		$sql = "SELECT DISTINCT p.product_id, p.product_name, p.description, p.price, 
		               p.quantity, p.is_available, b.business_name, b.business_id,
		               (SELECT pi.image_url FROM product_images pi 
		                WHERE pi.product_id = p.product_id 
		                ORDER BY pi.sort_order ASC, pi.image_id ASC 
		                LIMIT 1) as first_image,
		               (SELECT pi.thumb_url FROM product_images pi 
		                WHERE pi.product_id = p.product_id 
		                ORDER BY pi.sort_order ASC, pi.image_id ASC 
		                LIMIT 1) as first_thumb,
		               (SELECT pi.blur_url FROM product_images pi 
		                WHERE pi.product_id = p.product_id 
		                ORDER BY pi.sort_order ASC, pi.image_id ASC 
		                LIMIT 1) as first_blur
		        FROM products p
		        INNER JOIN businesses b ON p.business_id = b.business_id
		        INNER JOIN product_categories pc ON p.product_id = pc.product_id
		        WHERE p.is_available = 'True'
		          AND pc.category_name = ?
		          AND p.product_id != ?
		        ORDER BY RAND()
		        LIMIT ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$category, $this->productId, $limit]);
		
		$relatedProducts = [];
		foreach ($result as $row) {
			$images = [];
			if (!empty($row['first_image'])) {
				$images[] = [
					'url' => $row['first_image'],
					'thumb' => !empty($row['first_thumb']) ? $row['first_thumb'] : $row['first_image'],
					'blur' => !empty($row['first_blur']) ? $row['first_blur'] : $row['first_image']
				];
			}
			
			$relatedProducts[] = [
				'id' => $row['product_id'],
				'name' => $row['product_name'],
				'description' => $row['description'],
				'price' => $row['price'],
				'stockQuantity' => $row['quantity'],
				'isActive' => $row['is_available'],
				'businessName' => $row['business_name'],
				'businessId' => $row['business_id'],
				'formattedPrice' => '$' . number_format($row['price'], 2),
				'images' => $images
			];
		}
		
		return $relatedProducts;
	}
}
?>
