-- 1. INSERT/ADD: Insert a new product (succulents) for 'Green Thumb Nursery'.

INSERT INTO products (business_id, product_name, description, price, quantity, is_available)
VALUES
(
  (SELECT business_id FROM businesses WHERE business_name = 'Green Thumb Nursery'),
  'Mini Succulent Collection',
  'A collection of five low-maintenance succulents in small pots.',
  35.00,
  10,
  'True'
);

-- 2. UPDATE: Increase the price of the 'Abstract Oil Painting' by $10.00.
UPDATE products
SET price = 600.00
WHERE product_id = '5';

SELECT * from products;
