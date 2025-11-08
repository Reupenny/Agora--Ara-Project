<?php
/*
 * Cart View
 * This view is responsible for displaying the shopping cart.
 */

class CartView extends AbstractView
{
    private $cart;
    private $cartItems = [];
    private $cartTotal = 0;
    private $errorMessage = '';
    
    public function setCart($cart)
    {
        $this->cart = $cart;
    }
    
    public function setCartItems($items)
    {
        $this->cartItems = $items;
    }
    
    public function setCartTotal($total)
    {
        $this->cartTotal = $total;
    }
    
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }
    
    public function prepare()
    {
        $this->setTemplate('html/masterPage.html');
        $this->setTemplateField('pagename', 'Shopping Cart');
        
        $content = '<h1>Shopping Cart</h1>';
        
        if (!empty($this->errorMessage)) {
            $content .= '<div class="status-box status-inactive">
                <strong>Error:</strong> ' . htmlspecialchars($this->errorMessage) . '
            </div>';
        }
        
        if (empty($this->cartItems)) {
            $content .= '<div class="empty-state">
                <p>Your cart is empty</p>
                <a href="##site##shop" class="btn-primary">Continue Shopping</a>
            </div>';
        } else {
            $content .= '<div class="cart-layout">
                <div class="cart-items">';
            
            foreach ($this->cartItems as $item) {
                $itemTotal = $item['quantity'] * $item['item_price'];
                $imageUrl = $item['image_url'] ?? 'assets/images/tile.webp';
                $isAvailable = $item['is_available'] === 'True';
                $inStock = $item['quantity'] <= $item['stock_quantity'];
                
                $content .= '<div class="cart-item">
                    <div class="item-image">
                        <a href="##site##product/' . $item['product_id'] . '">
                            <img src="##site##' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($item['product_name']) . '">
                        </a>
                    </div>
                    <div class="item-info">
                        <h3><a href="##site##product/' . $item['product_id'] . '">' . htmlspecialchars($item['product_name']) . '</a></h3>
                        <p>by ' . htmlspecialchars($item['business_name']) . '</p>
                        <p class="item-price">$' . number_format($item['item_price'], 2) . '</p>';
                
                if (!$isAvailable) {
                    $content .= '<p class="item-warning">No longer available</p>';
                } elseif (!$inStock) {
                    $content .= '<p class="item-warning"> Insufficient stock (only ' . $item['stock_quantity'] . ' available)</p>';
                }
                
                $content .= '</div>
                    <div class="item-actions">
                        <form method="post" class="quantity-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="' . $item['product_id'] . '">
                            <label for="qty_' . $item['product_id'] . '">Qty:</label>
                            <input type="number" name="quantity" id="qty_' . $item['product_id'] . '" value="' . $item['quantity'] . '" min="1" max="' . $item['stock_quantity'] . '">
                            <button type="submit" class="btn-secondary">Update</button>
                        </form>
                        <p class="item-total">$' . number_format($itemTotal, 2) . '</p>
                        <form method="post">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="' . $item['product_id'] . '">
                            <button type="submit" class="remove-btn">Remove</button>
                        </form>
                    </div>
                </div>';
            }
            
            $content .= '</div>
                <div class="order-summary">
                    <div class="order-summary-card sticky">
                        <h3>Order Summary</h3>
                        <div class="order-summary-divider">
                            <div class="order-summary-row">
                                <span>Subtotal:</span>
                                <span>$' . number_format($this->cartTotal, 2) . '</span>
                            </div>
                            <div class="order-summary-row">
                                <span>Shipping:</span>
                                <span>TBD</span>
                            </div>
                            <div class="order-summary-total">
                                <span>Total:</span>
                                <span>$' . number_format($this->cartTotal, 2) . '</span>
                            </div>
                        </div>
                        <form method="post">
                            <input type="hidden" name="action" value="checkout">
                            <button type="submit" class="btn-primary" style="width:100%;padding:15px;font-size:1.1rem">Proceed to Checkout</button>
                        </form>
                        <a href="##site##shop" class="continue-shopping">← Continue Shopping</a>
                    </div>
                </div>
            </div>';
        }
        
        $this->setTemplateField('content', $content);
    }
}
?>
