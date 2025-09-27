SELECT DISTINCT
  p.product_name AS 'Product Name',
  p.price AS 'Price',
  b.business_name AS 'Seller'
FROM products p
JOIN businesses b
  ON p.business_id = b.business_id
LEFT JOIN product_tags pt
  ON p.product_id = pt.product_id
WHERE
  p.is_available = 'True' -- Only available products
  AND
  (
    pt.tag_name LIKE '%armchair%' -- Search by tag
    OR p.product_name LIKE '%armchair%' -- Search product name
    OR p.description LIKE '%armchair%' -- Search product description
  )
ORDER BY
  p.price DESC;



