<?php
/*
    Orders View
    Displays user's order history
*/

class OrdersView extends AbstractView
{
    private $orders = [];
    private $user;
    
    public function setOrders($orders)
    {
        $this->orders = $orders;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
    }
    
    public function prepare()
    {
        $this->setTemplate('html/masterPage.html');
        $this->setTemplateField('pagename', 'My Orders');
        
        $content = '<h1>My Orders</h1>';
        
        if (empty($this->orders)) {
            $content .= '<div class="empty-state">
                <p>You haven\'t placed any orders yet</p>
                <a href="##site##shop" class="btn-primary">Start Shopping</a>
            </div>';
        } else {
            $content .= '<div class="orders-grid">';
            
            foreach ($this->orders as $order) {
                $statusClass = strtolower($order['status']);
                $statusText = htmlspecialchars($order['status']);
                $orderDate = date('F j, Y \a\t g:i A', strtotime($order['order_date']));
                $orderTotal = $order['total_amount'] ? '$' . number_format($order['total_amount'], 2) : 'TBD';
                $itemCount = $order['item_count'];
                
                $content .= '<div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Order #' . $order['order_id'] . '</h3>
                            <p class="order-date">Placed on ' . $orderDate . '</p>
                        </div>
                        <span class="order-status ' . $statusClass . '">' . $statusText . '</span>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-item">
                            <p class="detail-label">Total Amount</p>
                            <p class="detail-value">' . $orderTotal . '</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Items</p>
                            <p class="detail-value">' . $itemCount . ' item' . ($itemCount != 1 ? 's' : '') . '</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Status</p>
                            <p class="detail-value">' . $statusText . '</p>
                        </div>
                    </div>
                    
                    <div class="order-actions">';
                
                $content .= '<a href="##site##order/' . $order['order_id'] . '" class="btn-secondary">View Details</a>';
                
                
                $content .= '</div>
                </div>';
            }
            
            $content .= '</div>';
        }
        
        $this->setTemplateField('content', $content);
    }
}
?>
