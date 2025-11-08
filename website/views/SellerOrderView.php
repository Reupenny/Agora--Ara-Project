<?php
/*
    Seller Order View
    Displays order details for seller with status update form
*/

class SellerOrderView extends AbstractView
{
    private $order;
    private $orderItems = [];
    private $orderTotal = 0;
    private $canManage = false;
    private $errorMessage = '';
    private $successMessage = '';
    
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
    
    public function setCanManage($canManage)
    {
        $this->canManage = $canManage;
    }
    
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }
    
    public function setSuccessMessage($message)
    {
        $this->successMessage = $message;
    }
    
    public function prepare()
    {
        $this->setTemplate('html/masterPage.html');
        $this->setTemplateField('pagename', 'Manage Order #' . $this->order['order_id']);
        
        $orderId = $this->order['order_id'];
        $statusClass = strtolower($this->order['status']);
        $statusText = htmlspecialchars($this->order['status']);
        $orderDate = date('F j, Y \a\t g:i A', strtotime($this->order['order_date']));
        $buyerUsername = htmlspecialchars($this->order['buyer_username']);
        
        $content = '<a href="##site##seller-orders" class="back-link">← Back to Orders</a>
        <h1>Order #' . $orderId . '</h1>';
        
        if (!empty($this->successMessage)) {
            $content .= '<div class="status-box status-active">
                <strong>Success:</strong> ' . htmlspecialchars($this->successMessage) . '
            </div>';
        }
        
        if (!empty($this->errorMessage)) {
            $content .= '<div class="status-box status-inactive">
                <strong>Error:</strong> ' . htmlspecialchars($this->errorMessage) . '
            </div>';
        }
        
        $content .= '<div class="order-header-section">
            <div class="order-meta-grid">
                <div class="order-meta-item">
                    <p>Order placed on:</p>
                    <p>' . $orderDate . '</p>
                </div>
                <div class="order-meta-item">
                    <p>Buyer:</p>
                    <p>' . $buyerUsername . '</p>
                </div>
            </div>';
        
        if ($this->canManage) {
            $content .= '<form method="POST" class="order-status-form">
                <input type="hidden" name="action" value="update-status">
                <div class="order-status-form-grid">
                
                    <div>
                    <span class="order-status ' . $statusClass . '">' . $statusText . '</span>
                        <label for="status">Update Order Status</label>
                        <select name="status" id="status" required>
                            <option value="Pending"' . ($statusText === 'Pending' ? ' selected' : '') . '>Pending</option>
                            <option value="Processing"' . ($statusText === 'Processing' ? ' selected' : '') . '>Processing</option>
                            <option value="Shipped"' . ($statusText === 'Shipped' ? ' selected' : '') . '>Shipped</option>
                            <option value="Delivered"' . ($statusText === 'Delivered' ? ' selected' : '') . '>Delivered</option>
                            <option value="Cancelled"' . ($statusText === 'Cancelled' ? ' selected' : '') . '>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-primary">Update Status</button>
                    </div>
                </div>
            </form>';
        } else {
            $content .= '<div class="status-box status-inactive">
                <strong>⚠️ Limited Access</strong><br>
                <span>You can only manage orders for products from businesses where you are a Seller.</span>
            </div>';
        }
        
        $content .= '</div><h2>Order Items</h2>';
        
        $itemsByBusiness = [];
        foreach ($this->orderItems as $item) {
            $businessId = $item['business_id'];
            if (!isset($itemsByBusiness[$businessId])) {
                $itemsByBusiness[$businessId] = [
                    'name' => $item['business_name'],
                    'items' => [],
                    'is_seller' => $item['user_role'] === 'Seller'
                ];
            }
            $itemsByBusiness[$businessId]['items'][] = $item;
        }
        
        foreach ($itemsByBusiness as $businessId => $businessData) {
            $businessName = htmlspecialchars($businessData['name']);
            $isSeller = $businessData['is_seller'];
            
            $content .= '<div class="items-by-business">
                <h3 class="business-header">' . $businessName;
            if ($isSeller) {
                $content .= ' <span class="business-badge">YOUR BUSINESS</span>';
            }
            $content .= '</h3>';
            
            foreach ($businessData['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['item_price'];
                $imageUrl = $item['image_url'] ?? 'assets/images/tile.webp';
                
                $content .= '<div class="order-item">
                    <div class="item-image">
                        <img src="##site##' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($item['product_name']) . '">
                    </div>
                    <div class="item-info">
                        <h4>' . htmlspecialchars($item['product_name']) . '</h4>
                        <p>Price: $' . number_format($item['item_price'], 2) . '</p>
                        <p>Quantity: ' . $item['quantity'] . '</p>
                    </div>
                    <div class="item-total">
                        $' . number_format($itemTotal, 2) . '
                    </div>
                </div>';
            }
            
            $content .= '</div>';
        }
        
        $content .= '<div class="order-summary-card">
            <h3>Order Summary</h3>
            <div class="order-summary-divider">
                <div class="order-summary-row">
                    <span>Subtotal:</span>
                    <span>$' . number_format($this->orderTotal, 2) . '</span>
                </div>
                <div class="order-summary-total">
                    <span>Total:</span>
                    <span>$' . number_format($this->orderTotal, 2) . '</span>
                </div>
            </div>
        </div>';
        
        $content = str_replace('##site##', $this->getSiteURL(), $content);
        $this->setTemplateField('content', $content);
    }
    
    private function getSiteURL()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $scriptDir . '/';
    }
}
?>
