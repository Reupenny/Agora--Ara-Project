<?php
/*
    Seller Orders View
    Displays all orders for seller's products
*/

class SellerOrdersView extends AbstractView
{
    public function prepare()
    {
        $this->setTemplate('html/masterPage.html');
        $this->setTemplateField('pagename', 'Seller Orders');
        
        $model = $this->getModel();
        $orders = $model->getOrders();
        
        $content = '<h1>Seller Orders</h1>
        <p class="empty-state">Manage orders containing your products</p>';
        
        if (empty($orders)) {
            $content .= '<div class="order-header-section">
                <h2>No Orders Yet</h2>
                <p>Orders containing your products will appear here.</p>
            </div>';
        } else {
            $content .= '<div class="orders-grid">';
            
            foreach ($orders as $order) {
                $statusClass = strtolower($order['status']);
                $statusText = htmlspecialchars($order['status']);
                $orderDate = date('F j, Y', strtotime($order['order_date']));
                $orderTotal = $order['total_amount'] ? '$' . number_format($order['total_amount'], 2) : 'TBD';
                $itemCount = $order['item_count'];
                $buyerUsername = htmlspecialchars($order['buyer_username']);
                $businessNames = htmlspecialchars($order['business_names']);
                
                $content .= '<div class="order-card">
                    <div class="order-header">
                        <h3>Order #' . $order['order_id'] . '</h3>
                        <span class="order-status ' . $statusClass . '">' . $statusText . '</span>
                    </div>
                    <div class="order-details">
                        <p><strong>Date:</strong> ' . $orderDate . '</p>
                        <p><strong>Buyer:</strong> ' . $buyerUsername . '</p>
                        <p><strong>Total:</strong> ' . $orderTotal . '</p>
                        <p><strong>Items:</strong> ' . $itemCount . '</p>
                        <p><strong>Business:</strong> ' . $businessNames . '</p>
                        <div class="order-actions">
                            <a href="##site##seller-order/' . $order['order_id'] . '" class="btn-primary">View&nbsp;Order</a>
                        </div>
                    </div>
                </div>';
            }
            
            $content .= '</div>';
        }
        
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
