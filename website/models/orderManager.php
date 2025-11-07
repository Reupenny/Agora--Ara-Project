<?php
/*
    Order Manager Model
    Handles cart and order operations
*/

class OrderManager extends AbstractModel
{
    /**
     * Get or create cart for user
     */
    public function getCart($username)
    {
        // Look for any existing carts for this user (ordered by newest first)
        $query = "SELECT * FROM orders WHERE buyer_username = ? AND status = 'Cart' ORDER BY order_date DESC";
        $results = $this->getDB()->queryPrepared($query, [$username]);

        if (!empty($results)) {
            // If multiple carts exist (legacy bug), merge items into the newest cart and remove duplicates
            $mainCart = $results[0];
            $mainOrderId = $mainCart['order_id'];

            if (count($results) > 1) {
                for ($i = 1; $i < count($results); $i++) {
                    $otherOrder = $results[$i];
                    $otherOrderId = $otherOrder['order_id'];

                    // Move items from other cart into main cart
                    $otherItems = $this->getDB()->queryPrepared("SELECT * FROM order_items WHERE order_id = ?", [$otherOrderId]);
                    foreach ($otherItems as $item) {
                        $existing = $this->getDB()->queryPrepared(
                            "SELECT * FROM order_items WHERE order_id = ? AND product_id = ?",
                            [$mainOrderId, $item['product_id']]
                        );

                        if (!empty($existing)) {
                            $newQty = $existing[0]['quantity'] + $item['quantity'];
                            $this->getDB()->executePrepared(
                                "UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ?",
                                [$newQty, $mainOrderId, $item['product_id']]
                            );
                        } else {
                            $this->getDB()->executePrepared(
                                "INSERT INTO order_items (order_id, product_id, quantity, item_price) VALUES (?, ?, ?, ?)",
                                [$mainOrderId, $item['product_id'], $item['quantity'], $item['item_price']]
                            );
                        }
                    }

                    // Remove the other cart and its items
                    $this->getDB()->executePrepared("DELETE FROM order_items WHERE order_id = ?", [$otherOrderId]);
                    $this->getDB()->executePrepared("DELETE FROM orders WHERE order_id = ?", [$otherOrderId]);
                }
            }

            return $mainCart;
        }

        // No cart found — create a new one
        $query = "INSERT INTO orders (buyer_username, status, order_date) VALUES (?, 'Cart', NOW())";
        $this->getDB()->executePrepared($query, [$username]);

        return [
            'order_id' => $this->getDB()->getInsertId(),
            'buyer_username' => $username,
            'status' => 'Cart',
            'total_amount' => 0
        ];
    }
    
    /**
     * Get cart items with product details
     */
    public function getCartItems($orderId)
    {
        $query = "SELECT oi.*, p.product_name, p.price, p.quantity as stock_quantity, 
                         p.is_available, b.business_name,
                         (SELECT pi.image_url FROM product_images pi 
                          WHERE pi.product_id = p.product_id 
                          ORDER BY pi.sort_order ASC, pi.image_id ASC 
                          LIMIT 1) as image_url
                  FROM order_items oi
                  INNER JOIN products p ON oi.product_id = p.product_id
                  INNER JOIN businesses b ON p.business_id = b.business_id
                  WHERE oi.order_id = ?
                  ORDER BY oi.product_id";
        
        return $this->getDB()->queryPrepared($query, [$orderId]);
    }
    
    /**
     * Add item to cart
     */
    public function addToCart($username, $productId, $quantity = 1)
    {
        // Get or create cart
        $cart = $this->getCart($username);
        $orderId = $cart['order_id'];
        
        // Get product price
        $productQuery = "SELECT price, quantity as stock FROM products WHERE product_id = ? AND is_available = 'True'";
        $productResult = $this->getDB()->queryPrepared($productQuery, [$productId]);
        
        if (empty($productResult)) {
            throw new Exception('Product not available.');
        }
        
        $product = $productResult[0];
        
        // Check if item already in cart
        $checkQuery = "SELECT * FROM order_items WHERE order_id = ? AND product_id = ?";
        $existing = $this->getDB()->queryPrepared($checkQuery, [$orderId, $productId]);
        
        if (!empty($existing)) {
            // Update quantity
            $newQuantity = $existing[0]['quantity'] + $quantity;
            
            // Check stock
            if ($newQuantity > $product['stock']) {
                throw new Exception('Not enough stock available.');
            }
            
            $updateQuery = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ?";
            return $this->getDB()->executePrepared($updateQuery, [$newQuantity, $orderId, $productId]);
        } else {
            // Check stock
            if ($quantity > $product['stock']) {
                throw new Exception('Not enough stock available.');
            }
            
            // Insert new item
            $insertQuery = "INSERT INTO order_items (order_id, product_id, quantity, item_price) VALUES (?, ?, ?, ?)";
            return $this->getDB()->executePrepared($insertQuery, [$orderId, $productId, $quantity, $product['price']]);
        }
    }
    
    /**
     * Update cart item quantity
     */
    public function updateCartItem($orderId, $productId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->removeCartItem($orderId, $productId);
        }
        
        // Check stock
        $productQuery = "SELECT quantity as stock FROM products WHERE product_id = ?";
        $productResult = $this->getDB()->queryPrepared($productQuery, [$productId]);
        
        if (!empty($productResult) && $quantity > $productResult[0]['stock']) {
            throw new Exception('Not enough stock available.');
        }
        
        $query = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ?";
        return $this->getDB()->executePrepared($query, [$quantity, $orderId, $productId]);
    }
    
    /**
     * Remove item from cart
     */
    public function removeCartItem($orderId, $productId)
    {
        $query = "DELETE FROM order_items WHERE order_id = ? AND product_id = ?";
        return $this->getDB()->executePrepared($query, [$orderId, $productId]);
    }
    
    /**
     * Calculate cart total
     */
    public function calculateCartTotal($orderId)
    {
        $query = "SELECT SUM(quantity * item_price) as total FROM order_items WHERE order_id = ?";
        $result = $this->getDB()->queryPrepared($query, [$orderId]);
        
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Checkout - convert cart to pending order
     */
    public function checkout($orderId)
    {
        // Calculate total
        $total = $this->calculateCartTotal($orderId);
        
        if ($total <= 0) {
            throw new Exception('Cart is empty.');
        }
        
        // Check all items are still available and have sufficient stock
        $items = $this->getCartItems($orderId);
        foreach ($items as $item) {
            if ($item['is_available'] !== 'True') {
                throw new Exception('Product "' . $item['product_name'] . '" is no longer available.');
            }
            if ($item['quantity'] > $item['stock_quantity']) {
                throw new Exception('Not enough stock for "' . $item['product_name'] . '". Only ' . $item['stock_quantity'] . ' available.');
            }
        }
        
        // Deduct stock quantities for all items
        foreach ($items as $item) {
            $updateStockQuery = "UPDATE products SET quantity = quantity - ? WHERE product_id = ?";
            $this->getDB()->executePrepared($updateStockQuery, [$item['quantity'], $item['product_id']]);
        }
        
        // Update order status to Pending (can be edited) and set total
        $query = "UPDATE orders SET status = 'Pending', total_amount = ?, order_date = NOW() WHERE order_id = ?";
        return $this->getDB()->executePrepared($query, [$total, $orderId]);
    }
    
    /**
     * Get user's orders
     */
    public function getUserOrders($username, $excludeCart = true)
    {
        $query = "SELECT o.*, COUNT(oi.product_id) as item_count
                  FROM orders o
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id
                  WHERE o.buyer_username = ?";
        
        if ($excludeCart) {
            $query .= " AND o.status != 'Cart'";
        }
        
        $query .= " GROUP BY o.order_id ORDER BY o.order_date DESC";
        
        return $this->getDB()->queryPrepared($query, [$username]);
    }
    
    /**
     * Get order details
     */
    public function getOrder($orderId)
    {
        $query = "SELECT * FROM orders WHERE order_id = ?";
        $result = $this->getDB()->queryPrepared($query, [$orderId]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Get order items with details
     */
    public function getOrderItems($orderId)
    {
        return $this->getCartItems($orderId); // Same query works for both
    }
    
    /**
     * Check if user can modify order
     */
    public function canModifyOrder($orderId, $username)
    {
        $order = $this->getOrder($orderId);
        
        if (!$order || $order['buyer_username'] !== $username) {
            return false;
        }
        
        // Can only modify pending orders (not processing, shipped, etc.)
        return $order['status'] === 'Pending';
    }
}
?>
