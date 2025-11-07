-- Update orders table to support cart functionality
-- Add 'Cart' and 'Processing' statuses to the orders table

USE agora_db;

-- Modify the status enum to include Cart and Processing
ALTER TABLE orders 
MODIFY COLUMN status ENUM('Cart', 'Processing', 'Pending', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Cart';

-- Make total_amount nullable since cart items won't have a final total yet
ALTER TABLE orders
MODIFY COLUMN total_amount DECIMAL(10,2) DEFAULT NULL;

-- Show updated structure
DESCRIBE orders;
