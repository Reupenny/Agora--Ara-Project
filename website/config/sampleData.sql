USE agora_db;

-- Insert Sample Users
INSERT INTO users (username, email, first_name, last_name, password_hash, account_type)
VALUES
('alice_buyer', 'alice@example.com', 'Alice', 'Smith', '$2y$12$48F4SbgtgAkdhbTc9l/m6eMcdj8B7wfnGGNtMwtNHwpRoT2oaQeje', 'Buyer'),
('bob_admin', 'bob@example.com', 'Bob', 'Jones', '$2y$12$48F4SbgtgAkdhbTc9l/m6eMcdj8B7wfnGGNtMwtNHwpRoT2oaQeje', 'Seller'),
('bob_buyer', 'bob@example.com', 'Bob', 'Jones', '$2y$12$48F4SbgtgAkdhbTc9l/m6eMcdj8B7wfnGGNtMwtNHwpRoT2oaQeje', 'Buyer'),
('bob_seller', 'bob@example.com', 'Bob', 'Jones', '$2y$12$48F4SbgtgAkdhbTc9l/m6eMcdj8B7wfnGGNtMwtNHwpRoT2oaQeje', 'Seller'),
('charlie_admin', 'charlie@example.com', 'Charlie', 'Brown', '$2y$12$48F4SbgtgAkdhbTc9l/m6eMcdj8B7wfnGGNtMwtNHwpRoT2oaQeje', 'Seller'),
('charlie_seller', 'charlie@example.com', 'Charlie', 'Brown', '$2y$12$48F4SbgtgAkdhbTc9l/m6eMcdj8B7wfnGGNtMwtNHwpRoT2oaQeje', 'Seller'),
('admin', 'diana@example.com', 'Diana', 'Prince', '$2y$12$48F4SbgtgAkdhbTc9l/m6eMcdj8B7wfnGGNtMwtNHwpRoT2oaQeje', 'Agora Admin');

-- Insert Sample Businesses
INSERT INTO businesses (business_name, business_location, details, is_active, short_description, business_email, business_phone)
VALUES
('The Vincategorye Emporium', 'Auckland, NZ', 'Discover a timeless collection of meticulously sourced **vincategorye furniture, unique home decor**, and classic apparel, offering sustainable style and character for every corner of your home. We specialize in pieces from the mid-20th century.', 'True', 'A curated collection of vincategorye furniture and decor.', 'info@vincategoryeemporium.nz', '09-123-4567'),
('Green Thumb Nursery', 'Christchurch, NZ', 'A vibrant garden center offering a wide variety of **locally grown native plants, lush indoor greenery**, high-quality gardening tools, and expert advice to help you cultivate your perfect space, whether you have a small balcony or a large plot.', 'True', 'Your go-to place for all things plants.', 'info@greenthumb.nz', '03-987-6543'),
('Canvas & Clay Gallery', 'Wellington, NZ', 'A contemporary art space dedicated to promoting **emerging and established local New Zealand artists**. We feature rotating exhibitions of paintings, sculptures, ceramics, and photography, hosting regular workshops and artist talks for the community.', 'True', 'Showcasing local artists and their work.', 'info@canvasclay.nz', '04-321-0987');

-- Insert Business Associations (linking sellers to businesses)
-- Bob is an Administrator for The Vincategorye Emporium.
INSERT INTO business_association
VALUES
('bob_admin', (SELECT business_id FROM businesses WHERE business_name = 'The Vincategorye Emporium'), 'Administrator', 'True');

INSERT INTO business_association
VALUES
('bob_seller', (SELECT business_id FROM businesses WHERE business_name = 'The Vincategorye Emporium'), 'Seller', 'True');

-- Charlie is a Seller for Green Thumb Nursery.
INSERT INTO business_association
VALUES
('charlie_seller', (SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'), 'Seller', 'True');

-- Charlie is a Seller for Green Thumb Nursery.
INSERT INTO business_association
VALUES
('charlie_admin', (SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'), 'Administrator', 'True');

-- Insert Sample Products
INSERT INTO products (business_id, product_name, description, price, quantity, is_available)
VALUES
((SELECT business_id FROM businesses WHERE business_name = 'The Vincategorye Emporium'), 'Mid-Century Teak Armchair', 'A stunning vincategorye armchair from the 1960s.', 250.00, 1, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'The Vincategorye Emporium'), 'Art Deco Glass Vase', 'A rare geometric vase from the 1920s.', 75.50, 3, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'), 'Variegated Monstera', 'A rare variegated Monstera deliciosa plant.', 120.00, 5, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'), 'Bonsai Tree', 'A small, meticulously cared for bonsai tree.', 85.00, 2, 'True'),
((SELECT business_id FROM businesses WHERE business_name = 'Canvas & Clay Gallery'), 'Abstract Oil Painting', 'A large original abstract painting on canvas.', 500.00, 1, 'True');

-- Insert Sample Categories
INSERT INTO categories (category_name)
VALUES
('plants'), ('ceramic'), ('indoor'), ('outdoor'), ('decorative'), 
('functional'), ('modern'), ('vincategorye'), ('handmade'), ('eco-friendly'),
('mid-century'), ('teak'), ('art-deco'), ('glassware'),
('rare'), ('bonsai'), ('art'), ('oil-painting'), ('abstract');

-- Insert Product Categories (junction table)
-- Associate the Vincategorye Armchair with categories
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'vincategorye'),
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'mid-century'),
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'teak');

-- Associate the Art Deco Vase with categories
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'vincategorye'),
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'art-deco'),
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'glassware');

-- Associate the Variegated Monstera with categories
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Variegated Monstera'), 'plants'),
((SELECT product_id FROM products WHERE product_name = 'Variegated Monstera'), 'rare');

-- Associate the Abstract Oil Painting with categories
INSERT INTO product_categories
VALUES
((SELECT product_id FROM products WHERE product_name = 'Abstract Oil Painting'), 'art'),
((SELECT product_id FROM products WHERE product_name = 'Abstract Oil Painting'), 'oil-painting'),
((SELECT product_id FROM products WHERE product_name = 'Abstract Oil Painting'), 'abstract');

-- Insert Updated Product Feature Images
INSERT INTO product_images (product_id, image_url, thumb_url, blur_url, sort_order)
VALUES
-- Images for Mid-Century Teak Armchair (product_id 1)
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'assets/images/products/1/featured_1762559021.webp', 'assets/images/products/1/featured_1762559021_thumb.webp', 'assets/images/products/1/featured_1762559021_blur.webp', 0),
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'assets/images/products/1/gallery_1762564077_0.webp', 'assets/images/products/1/gallery_1762564077_0_thumb.webp', 'assets/images/products/1/gallery_1762564077_0_blur.webp', 2),
((SELECT product_id FROM products WHERE product_name = 'Mid-Century Teak Armchair'), 'assets/images/products/1/gallery_1762564182_0.webp', 'assets/images/products/1/gallery_1762564182_0_thumb.webp', 'assets/images/products/1/gallery_1762564182_0_blur.webp', 1),

-- Images for Art Deco Glass Vase (product_id 2)
((SELECT product_id FROM products WHERE product_name = 'Art Deco Glass Vase'), 'assets/images/products/2/featured_1762559045.webp', 'assets/images/products/2/featured_1762559045_thumb.webp', 'assets/images/products/2/featured_1762559045_blur.webp', 0),

-- Images for Variegated Monstera (product_id 3)
((SELECT product_id FROM products WHERE product_name = 'Variegated Monstera'), 'assets/images/products/3/featured_1762559280.webp', 'assets/images/products/3/featured_1762559280_thumb.webp', 'assets/images/products/3/featured_1762559280_blur.webp', 0),

-- Images for Bonsai Tree (product_id 4)
((SELECT product_id FROM products WHERE product_name = 'Bonsai Tree'), 'assets/images/products/4/featured_1762559261.webp', 'assets/images/products/4/featured_1762559261_thumb.webp', 'assets/images/products/4/featured_1762559261_blur.webp', 0),

-- Images for Abstract Oil Painting (product_id 5)
((SELECT product_id FROM products WHERE product_name = 'Abstract Oil Painting'), 'assets/images/products/5/featured_1762559406.webp', 'assets/images/products/5/featured_1762559406_thumb.webp', 'assets/images/products/5/featured_1762559406_blur.webp', 0);



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
