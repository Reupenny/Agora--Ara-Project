<?php
/*
	Products Model
	Handles product listing data
*/

class ProductsModel extends AbstractModel {
	
	private $products = [];
	private $categoryFilter = null;
	private $searchQuery = null;
	
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
		                LIMIT 1) as first_image
		        FROM products p
		        INNER JOIN businesses b ON p.business_id = b.business_id
		        WHERE p.is_available = 'True'";
		
		$params = [];
		
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
