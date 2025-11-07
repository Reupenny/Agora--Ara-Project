<?php
/*
    Seller Order Model
    Shows order details for seller with ability to update status
*/

class SellerOrderModel extends AbstractModel
{
    private $order;
    private $orderItems = [];
    private $orderTotal = 0;
    private $canManage = false;
    
    public function load($orderId, $username)
    {
        // Get order details
        $query = "SELECT * FROM orders WHERE order_id = ? AND status != 'Cart'";
        $result = $this->getDB()->queryPrepared($query, [$orderId]);
        
        if (empty($result)) {
            throw new Exception('Order not found.');
        }
        
        $this->order = $result[0];
        
        // Check if user is a seller for any products in this order
        $checkQuery = "SELECT COUNT(*) as can_manage
                       FROM order_items oi
                       INNER JOIN products p ON oi.product_id = p.product_id
                       INNER JOIN business_association bm ON p.business_id = bm.business_id
                       WHERE oi.order_id = ? AND bm.username = ? AND bm.role_name = 'Seller'";
        
        $checkResult = $this->getDB()->queryPrepared($checkQuery, [$orderId, $username]);
        $this->canManage = !empty($checkResult) && $checkResult[0]['can_manage'] > 0;
        
        // Get order items with product details
        $itemsQuery = "SELECT oi.*, p.product_name, p.price, p.quantity as stock_quantity, 
                              p.is_available, b.business_name, b.business_id,
                              (SELECT pi.image_url FROM product_images pi 
                               WHERE pi.product_id = p.product_id 
                               ORDER BY pi.sort_order ASC, pi.image_id ASC 
                               LIMIT 1) as image_url,
                              bm.role_name as user_role
                       FROM order_items oi
                       INNER JOIN products p ON oi.product_id = p.product_id
                       INNER JOIN businesses b ON p.business_id = b.business_id
                       LEFT JOIN business_association bm ON b.business_id = bm.business_id AND bm.username = ?
                       WHERE oi.order_id = ?
                       ORDER BY b.business_name, p.product_name";
        
        $this->orderItems = $this->getDB()->queryPrepared($itemsQuery, [$username, $orderId]);
        
        // Calculate total
        $totalQuery = "SELECT SUM(quantity * item_price) as total FROM order_items WHERE order_id = ?";
        $totalResult = $this->getDB()->queryPrepared($totalQuery, [$orderId]);
        $this->orderTotal = $totalResult[0]['total'] ?? 0;
    }
    
    public function getOrder()
    {
        return $this->order;
    }
    
    public function getOrderItems()
    {
        return $this->orderItems;
    }
    
    public function getOrderTotal()
    {
        return $this->orderTotal;
    }
    
    public function canManage()
    {
        return $this->canManage;
    }
    
    public function updateOrderStatus($orderId, $newStatus, $username)
    {
        // Verify seller can manage this order
        $checkQuery = "SELECT COUNT(*) as can_manage
                       FROM order_items oi
                       INNER JOIN products p ON oi.product_id = p.product_id
                       INNER JOIN business_association bm ON p.business_id = bm.business_id
                       WHERE oi.order_id = ? AND bm.username = ? AND bm.role_name = 'Seller'";
        
        $checkResult = $this->getDB()->queryPrepared($checkQuery, [$orderId, $username]);
        
        if (empty($checkResult) || $checkResult[0]['can_manage'] == 0) {
            throw new Exception('You do not have permission to manage this order.');
        }
        
        // Validate status transition
        $validStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            throw new Exception('Invalid order status.');
        }
        
        // Update order status
        $query = "UPDATE orders SET status = ? WHERE order_id = ?";
        return $this->getDB()->executePrepared($query, [$newStatus, $orderId]);
    }
}
?>
