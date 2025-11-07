-- DATABASE: agora_db
-- This script creates the database and all necessary tables for the Agora application.
drop database if exists agora_db;
CREATE DATABASE IF NOT EXISTS agora_db;
USE agora_db;

-- USERS
CREATE TABLE users
(
  username VARCHAR(255) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  account_type ENUM('Buyer', 'Seller', 'Agora Admin') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (username),
  UNIQUE KEY unique_email_account (email, account_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- BUSINESSES
CREATE TABLE businesses
(
  business_id INT NOT NULL AUTO_INCREMENT,
  business_name VARCHAR(255) NOT NULL,
  business_location VARCHAR(255) DEFAULT NULL,
  business_email VARCHAR(255) DEFAULT NULL,
  business_phone VARCHAR(50) DEFAULT NULL,
  short_description VARCHAR(500) DEFAULT NULL,
  details TEXT DEFAULT NULL,
  is_active ENUM('True', 'False') NOT NULL DEFAULT 'False',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- BUSINESS ASSOCIATIONS
-- This table links users to businesses with specific roles
CREATE TABLE business_association
(
  username VARCHAR(255) NOT NULL,
  business_id INT NOT NULL,
  role_name ENUM('Administrator', 'Seller') NOT NULL,
  is_active ENUM('True', 'False') NOT NULL DEFAULT 'True',
  PRIMARY KEY (username, business_id),
  FOREIGN KEY (username) REFERENCES users (username),
  FOREIGN KEY (business_id) REFERENCES businesses (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PRODUCTS
CREATE TABLE products
(
  product_id INT NOT NULL AUTO_INCREMENT,
  business_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  is_available ENUM('True', 'False') NOT NULL DEFAULT 'True',
  PRIMARY KEY (product_id),
  FOREIGN KEY (business_id) REFERENCES businesses (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CATEGORIES
CREATE TABLE categories
(
  category_name VARCHAR(255) NOT NULL,
  PRIMARY KEY (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PRODUCT CATEGORIES
-- This table links products to categories (many-to-many relationship)
CREATE TABLE product_categories
(
  product_id INT NOT NULL,
  category_name VARCHAR(255) NOT NULL,
  PRIMARY KEY (product_id, category_name),
  FOREIGN KEY (product_id) REFERENCES products (product_id),
  FOREIGN KEY (category_name) REFERENCES categories (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PRODUCT IMAGES
CREATE TABLE product_images
(
  image_id INT NOT NULL AUTO_INCREMENT,
  product_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  thumb_url VARCHAR(255) NOT NULL,
  blur_url VARCHAR(255) NOT NULL,
  sort_order INT,
  PRIMARY KEY (image_id),
  FOREIGN KEY (product_id) REFERENCES products (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ORDERS
CREATE TABLE orders
(
  order_id INT NOT NULL AUTO_INCREMENT,
  buyer_username VARCHAR(255) NOT NULL,
  order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(10,2) NOT NULL,
  status ENUM('Cart','Processing','Pending','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Cart',
  PRIMARY KEY (order_id),
  FOREIGN KEY (buyer_username) REFERENCES users (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ORDER ITEMS
CREATE TABLE order_items
(
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  item_price DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (order_id, product_id),
  FOREIGN KEY (order_id) REFERENCES orders (order_id),
  FOREIGN KEY (product_id) REFERENCES products (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
