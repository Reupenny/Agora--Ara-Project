-- Add additional business fields for enhanced business management
-- Run this migration to add email, phone, and short description fields
-- Note: logo and banner are stored in filesystem using business_id in path

USE agora_db;

-- Add new fields to businesses table
ALTER TABLE businesses
ADD COLUMN business_email VARCHAR(255) DEFAULT NULL AFTER business_location,
ADD COLUMN business_phone VARCHAR(50) DEFAULT NULL AFTER business_email,
ADD COLUMN short_description VARCHAR(500) DEFAULT NULL AFTER business_phone;

-- Show updated structure
DESCRIBE businesses;
