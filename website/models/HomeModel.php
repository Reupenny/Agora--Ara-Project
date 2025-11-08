<?php
/*
	Home Model
	Handles data for the home page
*/

class HomeModel extends AbstractModel {
	
	private $featuredProducts = [];
	private $featuredBusinesses = [];
	
	// Load featured products (random selection)
	public function loadFeaturedProducts($limit = 4) {
		$sql = "SELECT p.product_id, p.product_name, p.description, p.price, 
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
		        WHERE p.is_available = 'True' AND b.is_active = 'True'
		        ORDER BY RAND()
		        LIMIT ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$limit]);
		
		$this->featuredProducts = [];
		foreach ($result as $row) {
			$images = [];
			if (!empty($row['first_image'])) {
				$images[] = [
					'url' => $row['first_image'],
					'thumb' => !empty($row['first_thumb']) ? $row['first_thumb'] : $row['first_image'],
					'blur' => !empty($row['first_blur']) ? $row['first_blur'] : $row['first_image']
				];
			}
			
			$this->featuredProducts[] = [
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
	}
	
	// Load featured businesses (random selection)
	public function loadFeaturedBusinesses($limit = 4) {
		$sql = "SELECT business_id, business_name, business_location, details
		        FROM businesses
		        WHERE is_active = 'True'
		        ORDER BY RAND()
		        LIMIT ?";
		
		$result = $this->getDB()->queryPrepared($sql, [$limit]);
		
		$this->featuredBusinesses = [];
		foreach ($result as $row) {
			$this->featuredBusinesses[] = [
				'id' => $row['business_id'],
				'name' => $row['business_name'],
				'location' => $row['business_location'],
				'description' => $row['details']
			];
		}
	}
	
	// Getters
	public function getFeaturedProducts() { return $this->featuredProducts; }
	public function getFeaturedBusinesses() { return $this->featuredBusinesses; }
}
?>
