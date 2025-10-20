<?php
/*
	Business Model
	Handles business data from the database
*/

class BusinessModel extends AbstractModel {
	
	private $businessId;
	private $businessName;
	private $location;
	private $details;
	private $isActive;
	private $createdAt;
	private $products = [];
	
	// Load business from database
	public function load($businessId) {
		$this->businessId = $businessId;
		
		// Query to get business info
		$sql = "SELECT * FROM businesses WHERE business_id = ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$businessId]);
		
		if (count($result) === 0) {
			throw new InvalidRequestException('Business not found');
		}
		
		$row = $result[0];
		$this->businessName = $row['business_name'];
		$this->location = $row['business_location'];
		$this->details = $row['details'];
		$this->isActive = $row['is_active'];
		$this->createdAt = $row['created_at'];
		
		// Load products
		$this->loadProducts();
	}
	
	// Load business products
	private function loadProducts() {
		$sql = "SELECT p.product_id, p.product_name, p.description, p.price, 
		               p.quantity, p.is_available,
		               (SELECT pi.image_url FROM product_images pi 
		                WHERE pi.product_id = p.product_id 
		                ORDER BY pi.sort_order ASC, pi.image_id ASC 
		                LIMIT 1) as first_image
		        FROM products p
		        WHERE p.business_id = ?
		        ORDER BY p.product_id DESC";
		
		$result = $this->getDB()->queryPrepared($sql, [$this->businessId]);
		
		$this->products = [];
		foreach ($result as $row) {
			$images = [];
			if (!empty($row['first_image'])) {
				$images[] = [
					'url' => $row['first_image'],
					'thumb' => $row['first_image'],
					'blur' => $row['first_image']
				];
			}
			
			$this->products[] = [
				'id' => $row['product_id'],
				'name' => $row['product_name'],
				'description' => $row['description'],
				'price' => $row['price'],
				'stockQuantity' => $row['quantity'],
				'isActive' => $row['is_available'],
				'formattedPrice' => '$' . number_format($row['price'], 2),
				'images' => $images
			];
		}
	}
	
	// Getters
	public function getBusinessId() { return $this->businessId; }
	public function getBusinessName() { return $this->businessName; }
	public function getLocation() { return $this->location; }
	public function getDetails() { return $this->details; }
	public function getIsActive() { return $this->isActive; }
	public function getCreatedAt() { return $this->createdAt; }
	public function getProducts() { return $this->products; }
	
	public function getProductCount() {
		return count($this->products);
	}
}
?>
