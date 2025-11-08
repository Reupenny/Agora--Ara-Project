<?php
/*
	Products Model
	Handles product listing data
*/

class ProductsModel extends AbstractModel {
	
	private $products = [];
	private $categoryFilter = null;
	private $searchQuery = null;
	private $username = null;
	
	// Set the viewing user
	public function setUsername($username) {
		$this->username = $username;
	}
	
	// Set filter by category
	public function setCategoryFilter($category) {
		$this->categoryFilter = $category;
	}
	
	// Set search query
	public function setSearchQuery($query) {
		$this->searchQuery = $query;
	}
	
	// Load products from database
	public function loadProducts() {
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
		                LIMIT 1) as first_blur";
		
		// If username provided, check if user is a seller for the business
		if ($this->username) {
			$sql .= ", (SELECT COUNT(*) FROM business_association bm 
			            WHERE bm.business_id = b.business_id 
			            AND bm.username = ? 
			            AND bm.is_active = 'True') as is_seller";
		}
		
		$sql .= " FROM products p
		        INNER JOIN businesses b ON p.business_id = b.business_id
		        WHERE b.is_active = 'True' AND (p.is_available = 'True'";
		
		// Include draft products if user is a seller
		if ($this->username) {
			$sql .= " OR p.product_id IN (
			            SELECT p2.product_id FROM products p2
			            INNER JOIN business_association bm ON p2.business_id = bm.business_id
			            WHERE bm.username = ? AND bm.is_active = 'True'
			          ))";
		} else {
			$sql .= ")";
		}
		
		$params = [];
		if ($this->username) {
			$params[] = $this->username;
			$params[] = $this->username;
		}
		
		// Add category filter if set
		if ($this->categoryFilter) {
			$sql .= " AND p.product_id IN (
			            SELECT pc.product_id 
			            FROM product_categories pc
			            WHERE pc.category_name = ?
			          )";
			$params[] = $this->categoryFilter;
		}
		
		// Add search filter if set
		if ($this->searchQuery !== null) {
			$sql .= " AND (p.product_name LIKE ? OR p.description LIKE ? OR b.business_name LIKE ? OR p.product_id IN (
			            SELECT pc.product_id 
			            FROM product_categories pc
			            WHERE pc.category_name LIKE ?
			          ))";
			$searchTerm = '%' . $this->searchQuery . '%';
			$params[] = $searchTerm;
			$params[] = $searchTerm;
			$params[] = $searchTerm;
            $params[] = $searchTerm;
		}
		
		$sql .= " ORDER BY p.product_id DESC";
		
		// Execute query
		if (count($params) > 0) {
			$result = $this->getDB()->queryPrepared($sql, $params);
		} else {
			$result = $this->getDB()->query($sql);
		}
		
		// Fetch products
		$this->products = [];
		foreach ($result as $row) {
			$images = [];
			if (!empty($row['first_image'])) {
				$images[] = [
					'url' => $row['first_image'],
					'thumb' => !empty($row['first_thumb']) ? $row['first_thumb'] : $row['first_image'],
					'blur' => !empty($row['first_blur']) ? $row['first_blur'] : $row['first_image']
				];
			}
			
			// Add "DRAFT-" prefix if product is not available and user is a seller
			$productName = $row['product_name'];
			$isSeller = isset($row['is_seller']) && $row['is_seller'] > 0;
			if ($row['is_available'] !== 'True' && $isSeller) {
				$productName = 'DRAFT-' . $productName;
			}
			
			$this->products[] = [
				'id' => $row['product_id'],
				'name' => $productName,
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
	
	// Getters
	public function getProducts() { return $this->products; }
	public function getProductCount() { return count($this->products); }
	public function getCategoryFilter() { return $this->categoryFilter; }
	public function getSearchQuery() { return $this->searchQuery; }
}
?>
