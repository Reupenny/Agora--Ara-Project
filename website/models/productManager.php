<?php
/*
    Product Manager Model
    Handles product creation, update, and management operations
*/

class ProductManager extends AbstractModel
{
    /**
     * Get all available tags/categories
     */
    public function getAllTags()
    {
        $query = "SELECT category_name FROM categories ORDER BY category_name";
        $result = $this->getDB()->query($query);
        
        $tags = [];
        foreach ($result as $row) {
            $tags[] = $row['category_name'];
        }
        
        return $tags;
    }
    
    /**
     * Get product tags/categories
     */
    public function getProductTags($productId)
    {
        $query = "SELECT category_name FROM product_categories WHERE product_id = ?";
        $result = $this->getDB()->queryPrepared($query, [$productId]);
        
        $tags = [];
        foreach ($result as $row) {
            $tags[] = $row['category_name'];
        }
        
        return $tags;
    }
    
    /**
     * Get product by ID with all details
     */
    public function getProduct($productId)
    {
        $query = "SELECT p.*, b.business_name 
                  FROM products p
                  INNER JOIN businesses b ON p.business_id = b.business_id
                  WHERE p.product_id = ?";
        
        $result = $this->getDB()->queryPrepared($query, [$productId]);
        
        if (!empty($result)) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Get featured image (first image)
     */
    public function getFeaturedImage($productId)
    {
        $query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order LIMIT 1";
        $result = $this->getDB()->queryPrepared($query, [$productId]);
        
        if (!empty($result)) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Get product images
     */
    public function getProductImages($productId)
    {
        $query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order";
        $result = $this->getDB()->queryPrepared($query, [$productId]);
        
        return $result;
    }
    
    /**
     * Create a new product
     */
    public function createProduct($businessId, $productName, $description, $price, $quantity, $isAvailable)
    {
        $query = "INSERT INTO products (business_id, product_name, description, price, quantity, is_available) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $result = $this->getDB()->executePrepared(
            $query, 
            [$businessId, $productName, $description, $price, $quantity, $isAvailable]
        );
        
        if ($result) {
            return $this->getDB()->getInsertId();
        }
        
        return false;
    }
    
    /**
     * Update an existing product
     */
    public function updateProduct($productId, $productName, $description, $price, $quantity, $isAvailable)
    {
        $query = "UPDATE products 
                  SET product_name = ?, description = ?, price = ?, quantity = ?, is_available = ?
                  WHERE product_id = ?";
        
        return $this->getDB()->executePrepared(
            $query, 
            [$productName, $description, $price, $quantity, $isAvailable, $productId]
        );
    }
    
    /**
     * Add tags to a product
     */
    public function addProductTags($productId, $tags)
    {
        // First, remove existing tags
        $this->removeProductTags($productId);
        
        // Then add new tags
        if (!empty($tags)) {
            $query = "INSERT INTO product_categories (product_id, category_name) VALUES (?, ?)";
            
            foreach ($tags as $tag) {
                $this->getDB()->executePrepared($query, [$productId, $tag]);
            }
        }
        
        return true;
    }
    
    /**
     * Remove all tags from a product
     */
    private function removeProductTags($productId)
    {
        $query = "DELETE FROM product_categories WHERE product_id = ?";
        return $this->getDB()->executePrepared($query, [$productId]);
    }
    
    /**
     * Add a product image
     */
    public function addProductImage($productId, $imageUrl, $thumbUrl, $blurUrl, $sortOrder = 0)
    {
        $query = "INSERT INTO product_images (product_id, image_url, thumb_url, blur_url, sort_order) 
                  VALUES (?, ?, ?, ?, ?)";
        
        return $this->getDB()->executePrepared(
            $query, 
            [$productId, $imageUrl, $thumbUrl, $blurUrl, $sortOrder]
        );
    }
    
    /**
     * Get user's business ID (if they have an active business association as Seller)
     */
    public function getUserBusinessId($username)
    {
        $query = "SELECT business_id 
                  FROM business_association 
                  WHERE username = ? AND is_active = 'True' AND role_name = 'Seller'
                  LIMIT 1";
        
        $result = $this->getDB()->queryPrepared($query, [$username]);
        
        if (!empty($result)) {
            return $result[0]['business_id'];
        }
        
        return null;
    }
    
    /**
     * Verify user has permission to edit this product (must be a Seller)
     */
    public function userCanEditProduct($username, $productId)
    {
        $query = "SELECT p.product_id 
                  FROM products p
                  INNER JOIN business_association ba ON p.business_id = ba.business_id
                  WHERE p.product_id = ? AND ba.username = ? AND ba.is_active = 'True' AND ba.role_name = 'Seller'";
        
        $result = $this->getDB()->queryPrepared($query, [$productId, $username]);
        
        return !empty($result);
    }
    
    /**
     * Delete a product (and its associated data)
     */
    public function deleteProduct($productId)
    {
        // Delete images first
        $query = "DELETE FROM product_images WHERE product_id = ?";
        $this->getDB()->executePrepared($query, [$productId]);
        
        // Delete tags
        $this->removeProductTags($productId);
        
        // Delete the product
        $query = "DELETE FROM products WHERE product_id = ?";
        return $this->getDB()->executePrepared($query, [$productId]);
    }
}
