<?php
/*
    Business Manager Model
    Handles business creation, updates, and associations
*/

class BusinessManagerModel extends AbstractModel
{
    /**
     * Create a new business
     */
    public function createBusiness($businessName, $businessLocation, $details, $creatorUsername, $email = null, $phone = null, $shortDescription = null)
    {
        // Create the business (initially inactive, pending admin approval)
        $query = "INSERT INTO businesses (business_name, business_location, business_email, business_phone, short_description, details, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, 'False')";
        
        $result = $this->getDB()->executePrepared($query, [$businessName, $businessLocation, $email, $phone, $shortDescription, $details]);
        
        if ($result) {
            $businessId = $this->getDB()->getInsertId();
            
            // Automatically associate the creator as Administrator
            $this->associateUserWithBusiness($creatorUsername, $businessId, 'Administrator', 'True');
            
            return $businessId;
        }
        
        return false;
    }
    
    /**
     * Update business details
     */
    public function updateBusiness($businessId, $businessName, $businessLocation, $details, $email = null, $phone = null, $shortDescription = null)
    {
        $query = "UPDATE businesses 
                  SET business_name = ?, business_location = ?, business_email = ?, business_phone = ?, short_description = ?, details = ?
                  WHERE business_id = ?";
        
        return $this->getDB()->executePrepared($query, [$businessName, $businessLocation, $email, $phone, $shortDescription, $details, $businessId]);
    }
    
    /**
     * Approve a business (admin only)
     */
    public function approveBusiness($businessId)
    {
        $query = "UPDATE businesses SET is_active = 'True' WHERE business_id = ?";
        return $this->getDB()->executePrepared($query, [$businessId]);
    }
    
    /**
     * Deactivate a business (admin only)
     */
    public function deactivateBusiness($businessId)
    {
        $query = "UPDATE businesses SET is_active = 'False' WHERE business_id = ?";
        return $this->getDB()->executePrepared($query, [$businessId]);
    }
    
    /**
     * Get business by ID
     */
    public function getBusiness($businessId)
    {
        $query = "SELECT * FROM businesses WHERE business_id = ?";
        $result = $this->getDB()->queryPrepared($query, [$businessId]);
        
        if (!empty($result)) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Get user's business (if they have one)
     */
    public function getUserBusiness($username)
    {
        $query = "SELECT b.*, ba.role_name, ba.is_active as association_active
                  FROM businesses b
                  INNER JOIN business_association ba ON b.business_id = ba.business_id
                  WHERE ba.username = ? AND ba.is_active = 'True'
                  LIMIT 1";
        
        $result = $this->getDB()->queryPrepared($query, [$username]);
        
        if (!empty($result)) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Associate user with a business
     */
    public function associateUserWithBusiness($username, $businessId, $role = 'Seller', $isActive = 'True')
    {
        $query = "INSERT INTO business_association (username, business_id, role_name, is_active) 
                  VALUES (?, ?, ?, ?)";
        
        return $this->getDB()->executePrepared($query, [$username, $businessId, $role, $isActive]);
    }
    
    /**
     * Check if user can edit business
     */
    public function userCanEditBusiness($username, $businessId)
    {
        $query = "SELECT * FROM business_association 
                  WHERE username = ? AND business_id = ? 
                  AND role_name = 'Administrator' AND is_active = 'True'";
        
        $result = $this->getDB()->queryPrepared($query, [$username, $businessId]);
        
        return !empty($result);
    }
    
    /**
     * Get all businesses (for admin)
     */
    public function getAllBusinesses($activeOnly = false)
    {
        if ($activeOnly) {
            $query = "SELECT * FROM businesses WHERE is_active = 'True' ORDER BY business_name";
        } else {
            $query = "SELECT * FROM businesses ORDER BY is_active DESC, business_name";
        }
        
        return $this->getDB()->query($query);
    }
    
    /**
     * Get pending businesses (awaiting approval)
     */
    public function getPendingBusinesses()
    {
        $query = "SELECT b.*, 
                         (SELECT u.username FROM business_association ba 
                          JOIN users u ON ba.username = u.username 
                          WHERE ba.business_id = b.business_id 
                          AND ba.role_name = 'Administrator' 
                          LIMIT 1) as owner_username
                  FROM businesses b
                  WHERE b.is_active = 'False'
                  ORDER BY b.created_at DESC";
        
        return $this->getDB()->query($query);
    }
    
    /**
     * Get business members
     */
    public function getBusinessMembers($businessId)
    {
        $query = "SELECT ba.*, u.first_name, u.last_name, u.email
                  FROM business_association ba
                  INNER JOIN users u ON ba.username = u.username
                  WHERE ba.business_id = ?
                  ORDER BY ba.role_name, u.username";
        
        return $this->getDB()->queryPrepared($query, [$businessId]);
    }
    
    /**
     * Check if business name already exists
     */
    public function businessNameExists($businessName, $excludeBusinessId = null)
    {
        if ($excludeBusinessId) {
            $query = "SELECT business_id FROM businesses WHERE business_name = ? AND business_id != ?";
            $result = $this->getDB()->queryPrepared($query, [$businessName, $excludeBusinessId]);
        } else {
            $query = "SELECT business_id FROM businesses WHERE business_name = ?";
            $result = $this->getDB()->queryPrepared($query, [$businessName]);
        }
        
        return !empty($result);
    }
    
    /**
     * Get business stats
     */
    public function getBusinessStats($businessId)
    {
        // Get total products
        $productQuery = "SELECT COUNT(*) as total_products FROM products WHERE business_id = ?";
        $productResult = $this->getDB()->queryPrepared($productQuery, [$businessId]);
        $totalProducts = $productResult[0]['total_products'] ?? 0;
        
        // Get total orders (if orders table has business_id or through products)
        // For now, return placeholder
        $totalOrders = 0;
        
        // Get business creation date
        $business = $this->getBusiness($businessId);
        $createdAt = $business['created_at'] ?? null;
        
        return [
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'created_at' => $createdAt
        ];
    }
    
    /**
     * Update or add business member role
     */
    public function updateBusinessMember($username, $businessId, $role, $isActive)
    {
        // Check if association exists
        $checkQuery = "SELECT * FROM business_association WHERE username = ? AND business_id = ?";
        $existing = $this->getDB()->queryPrepared($checkQuery, [$username, $businessId]);
        
        if (!empty($existing)) {
            // Update existing
            $query = "UPDATE business_association SET role_name = ?, is_active = ? WHERE username = ? AND business_id = ?";
            return $this->getDB()->executePrepared($query, [$role, $isActive, $username, $businessId]);
        } else {
            // Insert new
            return $this->associateUserWithBusiness($username, $businessId, $role, $isActive);
        }
    }
    
    /**
     * Remove business member
     */
    public function removeBusinessMember($username, $businessId)
    {
        $query = "DELETE FROM business_association WHERE username = ? AND business_id = ?";
        return $this->getDB()->executePrepared($query, [$username, $businessId]);
    }
}
