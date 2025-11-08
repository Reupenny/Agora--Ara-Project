<?php
/*
 * Seller Orders Model
 * This model is responsible for displaying all orders containing products from businesses where the user is a seller.
 */

class SellerOrdersModel extends AbstractModel
{
    private $orders = [];
    private $username;
    
    public function load($username)
    {
        $this->username = $username;
        
        // Get all orders that contain products from businesses where user is a seller
        // Exclude cart status orders
        $query = "SELECT DISTINCT o.order_id, o.buyer_username, o.order_date, o.status, o.total_amount,
                         COUNT(DISTINCT oi.product_id) as item_count,
                         GROUP_CONCAT(DISTINCT b.business_name SEPARATOR ', ') as business_names
                  FROM orders o
                  INNER JOIN order_items oi ON o.order_id = oi.order_id
                  INNER JOIN products p ON oi.product_id = p.product_id
                  INNER JOIN businesses b ON p.business_id = b.business_id
                  INNER JOIN business_association bm ON b.business_id = bm.business_id
                  WHERE bm.username = ? 
                    AND bm.role_name = 'Seller'
                    AND o.status != 'Cart'
                  GROUP BY o.order_id
                  ORDER BY o.order_date DESC";
        
        $this->orders = $this->getDB()->queryPrepared($query, [$username]);
    }
    
    public function getOrders()
    {
        return $this->orders;
    }
}
?>
