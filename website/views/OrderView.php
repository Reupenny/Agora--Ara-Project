<?php
/*
 * Order View
 * This view is responsible for displaying individual order details with editing capabilities.
 */

class OrderView extends AbstractView
{
    private $order;
    private $orderItems = [];
    private $orderTotal = 0;
    private $canEdit = false;
    private $errorMessage = '';
    
    public function setOrder($order)
    {
        $this->order = $order;
    }
    
    public function setOrderItems($items)
    {
        $this->orderItems = $items;
    }
    
    public function setOrderTotal($total)
    {
        $this->orderTotal = $total;
    }
    
    public function setCanEdit($canEdit)
    {
        $this->canEdit = $canEdit;
    }
    
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }
    
    public function prepare()
    {
        $this->setTemplate('html/masterPage.html');
        $this->setTemplateField('pagename', 'Order #' . $this->order['order_id']);
        
        $orderId = $this->order['order_id'];
        $statusClass = strtolower($this->order['status']);
        $statusText = htmlspecialchars($this->order['status']);
        $orderDate = date('F j, Y \a\t g:i A', strtotime($this->order['order_date']));
        
        $content = '<div class="back-link">
            <a href="##site##orders">← Back to Orders</a>
        </div>
        
        <h1>Order #' . $orderId . '</h1>';
        
        if (!empty($this->errorMessage)) {
            $content .= '<div class="status-box status-inactive">
                <strong>Error:</strong> ' . htmlspecialchars($this->errorMessage) . '
            </div>';
        }
        
        $content .= '<div class="order-header-section">
            <div class="order-meta-grid">
                <div class="order-meta-item">
                    <p class="meta-label">Order placed on</p>
                    <p class="meta-value">' . $orderDate . '</p>
                </div>
                <span class="order-status ' . $statusClass . '">' . $statusText . '</span>
            </div>';
    
        
        $content .= '</div>';
        
        $content .= '<h2>Order Items</h2>
        <div class="order-items">';
        
        foreach ($this->orderItems as $item) {
            $itemTotal = $item['quantity'] * $item['item_price'];
            $imageUrl = $item['image_url'] ?? 'assets/images/tile.webp';
            $isAvailable = $item['is_available'] === 'True';
            $inStock = $item['quantity'] <= $item['stock_quantity'];
            
            $content .= '<div class="order-item">
                <div class="item-image">
                    <a href="##site##product/' . $item['product_id'] . '">
                        <img src="##site##' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($item['product_name']) . '">
                    </a>
                </div>
                <div class="item-info">
                    <h3><a href="##site##product/' . $item['product_id'] . '">' . htmlspecialchars($item['product_name']) . '</a></h3>
                    <p>by ' . htmlspecialchars($item['business_name']) . '</p>
                    <p class="quantity">Quantity: ' . $item['quantity'] . '</p>
                    <p class="item-price">$' . number_format($item['item_price'], 2) . ' each</p>';
        
            
            $content .= '</div>
                <div class="item-actions">
                    <p class="item-total">$' . number_format($itemTotal, 2) . '</p>';
            
            if ($this->canEdit) {
                $content .= '<form method="post" class="quantity-form">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="' . $item['product_id'] . '">
                        <label for="qty_' . $item['product_id'] . '">Qty:</label>
                        <input type="number" name="quantity" id="qty_' . $item['product_id'] . '" value="' . $item['quantity'] . '" min="1" max="' . $item['stock_quantity'] . '">
                        <button type="submit" class="btn-secondary">Update</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="' . $item['product_id'] . '">
                        <button type="submit" class="remove-btn">Remove</button>
                    </form>';
            }
            
            $content .= '</div>
            </div>';
        }
        
        $content .= '</div>';
        
        $content .= '<div class="summary-wrapper">
            <div class="order-summary-card">
                <h3>Order Summary</h3>
                <div class="order-summary-divider">
                    <div class="order-summary-row">
                        <span>Subtotal:</span>
                        <span>$' . number_format($this->orderTotal, 2) . '</span>
                    </div>
                    <div class="order-summary-row">
                        <span>Shipping:</span>
                        <span>TBD</span>
                    </div>
                    <div class="order-summary-total">
                        <span>Total:</span>
                        <span>$' . number_format($this->orderTotal, 2) . '</span>
                    </div>
                </div>
            </div>
        </div>';
        
        $this->setTemplateField('content', $content);
    }
}
?>
