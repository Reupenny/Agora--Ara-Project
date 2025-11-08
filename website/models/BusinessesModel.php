<?php
/*
 * Businesses Model
 * This model is responsible for loading businesses for the listing page.
 */

class BusinessesModel extends AbstractModel {
	
	private $businesses = [];
	
	public function loadBusinesses() {
		$sql = "SELECT b.business_id, b.business_name, b.business_location, 
		               b.short_description, b.details, b.created_at,
		               COUNT(DISTINCT p.product_id) as product_count
		        FROM businesses b
		        LEFT JOIN products p ON b.business_id = p.business_id AND p.is_available = 'True'
		        WHERE b.is_active = 'True'
		        GROUP BY b.business_id
		        ORDER BY b.business_name ASC";
		
		$this->businesses = $this->getDB()->query($sql);
	}
	
	public function getBusinesses() {
		return $this->businesses;
	}
	
	public function getBusinessCount() {
		return count($this->businesses);
	}
}
?>
