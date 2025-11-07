USE agora_db;

-- Insert Sample Users
INSERT INTO users (username, email, first_name, last_name, password_hash, account_type)
VALUES
('alice_buyer', 'alice@example.com', 'Alice', 'Smith', 'hashed_pass_1', 'Buyer'),
('bob_seller', 'bob@example.com', 'Bob', 'Jones', 'hashed_pass_2', 'Seller'),
('bob_buyer', 'bob@example.com', 'Bob', 'Jones', 'hashed_pass_3', 'Buyer'),
('charlie_seller', 'charlie@example.com', 'Charlie', 'Brown', 'hashed_pass_4', 'Seller'),
('diana_admin', 'diana@example.com', 'Diana', 'Prince', 'hashed_pass_5', 'Agora Admin');

-- Insert Sample Businesses
INSERT INTO businesses (business_name, business_location, details, is_active)
VALUES
('The Vintage Emporium', 'Auckland, NZ', 'Legal LTD', 'True'),
('Green Thumb Nursery', 'Christchurch, NZ', 'Gardens Trust', 'True'),
('Canvas & Clay Gallery', 'Wellington, NZ', 'Arts Group Inc.', 'True');

-- Insert Business Associations (linking sellers to businesses)
-- Bob is an Administrator for The Vintage Emporium.
INSERT INTO business_association
VALUES
('bob_seller', (SELECT business_id FROM businesses WHERE business_name = 'The Vintage Emporium'), 'Administrator', 'True');

-- Charlie is a Seller for Green Thumb Nursery.
INSERT INTO business_association
VALUES
('charlie_seller', (SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'), 'Seller', 'True');

-- Insert Sample Products
INSERT INTO products (business_id, product_name, description, price, quantity, is_available)
VALUES
((SELECT business_id FROM businesses WHERE business_name = 'The Vintage Emporium'), 'Mid-Century Teak Armchair', 'A stunning vintage armchair from the 1960s.', 250.00, 1, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'The Vintage Emporium'), 'Art Deco Glass Vase', 'A rare geometric vase from the 1920s.', 75.50, 3, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'), 'Variegated Monstera', 'A rare variegated Monstera deliciosa plant.', 120.00, 5, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'), 'Bonsai Tree', 'A small, meticulously cared for bonsai tree.', 85.00, 2, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'Canvas & Clay Gallery'), 'Abstract Oil Painting', 'A large original abstract painting on canvas.', 500.00, 1, 'True');

-- Insert Sample Categories
INSERT INTO categories (category_name)
VALUES
('plants'), ('ceramic'), ('indoor'), ('outdoor'), ('decorative'), 
('functional'), ('modern'), ('vintage'), ('handmade'), ('eco-friendly'),
('mid-century'), ('teak'), ('art-deco'), ('glassware'),
('rare'), ('bonsai'), ('art'), ('oil-painting'), ('abstract');

-- Insert Product Categories (junction table)
-- Associate the Vintage Armchair with tags
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'vintage'),
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'mid-century'),
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'teak');

-- Associate the Art Deco Vase with tags
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'vintage'),
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'art deco'),
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'glassware');

-- Associate the Variegated Monstera with tags
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Variegated Monstera'), 'plants'),
((SELECT product_id FROM products WHERE product_name = 'Variegated Monstera'), 'rare');

-- Associate the Abstract Oil Painting with tags
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Abstract Oil Painting'), 'art'),
((SELECT product_id FROM products WHERE product_name = 'Abstract Oil Painting'), 'oil painting'),
((SELECT product_id FROM products WHERE product_name = 'Abstract Oil Painting'), 'abstract');

-- Insert Product Images
INSERT INTO product_images (product_id, image_url, thumb_url, blur_url, sort_order)
VALUES
-- Images for Mid-Century Teak Armchair (product_id 1)
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'assets/images/products/1/feature.webp', 'assets/images/products/1/feature.webp', 'assets/images/products/1/feature.webp', 0),
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'assets/images/products/1/1.webp', 'assets/images/products/1/1.webp', 'assets/images/products/1/1.webp', 1),

-- Images for Art Deco Glass Vase (product_id 2)
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'assets/images/products/2/feature.webp', 'assets/images/products/2/feature.webp', 'assets/images/products/2/feature.webp', 0),
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'assets/images/products/2/1.webp', 'assets/images/products/2/1.webp', 'assets/images/products/2/1.webp', 1),

-- Images for Variegated Monstera (product_id 3)
((SELECT product_id FROM products WHERE product_name = 'Variegated Monstera'), 'assets/images/products/3/feature.webp', 'assets/images/products/3/feature.webp', 'assets/images/products/3/feature.webp', 0),
((SELECT product_id FROM products WHERE product_name = 'Variegated Monstera'), 'assets/images/products/3/1.webp', 'assets/images/products/3/1.webp', 'assets/images/products/3/1.webp', 1);

-- Insert Sample Order and Order Items
INSERT INTO orders (buyer_username, total_amount, status)
VALUES
('alice_buyer', 335.50, 'Pending');

-- Add items to Alice's order.
INSERT INTO order_items ( order_id ,product_id, quantity, item_price)
VALUES
((SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1),
 (SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'),
 1, 250.00),
((SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1),
 (SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'),
 1, 85.50);
