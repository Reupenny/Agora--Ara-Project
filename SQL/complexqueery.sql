SELECT
    o.order_id AS 'Order ID',
    o.order_date AS 'Order Date',
    o.status AS 'Order Status',
    CONCAT(u.first_name, ' ', u.last_name) AS 'Customer Name',
    u.email AS 'Customer Email',
    oi.quantity AS 'Quantity',
    oi.item_price AS 'Item Price',
    p.product_name AS 'Product Name',
    b.business_name AS 'Seller Business'
FROM orders o
-- 1. Get Customer Details
JOIN users u
    ON o.buyer_username = u.username
-- 2. Get Products in the Order
JOIN order_items oi
    ON o.order_id = oi.order_id
-- 3. Get Product Details
JOIN products p
    ON oi.product_id = p.product_id
-- 4. Get Seller Business Details
JOIN businesses b
    ON p.business_id = b.business_id
ORDER BY
    o.order_id, b.business_name, p.product_name;





    