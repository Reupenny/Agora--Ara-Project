SELECT
  first_name AS "First Name",
  last_name AS "Last Name",
  email AS "Email",
  account_type AS "Account Type",
  DATEDIFF(CURDATE(), created_at) AS "Account Age (Days)"
FROM users
WHERE account_type = 'Buyer'
ORDER BY last_name, first_name;





