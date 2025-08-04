-- MySQL initialization script
-- This script ensures that the app_user can connect from any host within the Docker network

-- Create user if not exists (MySQL 8.0 compatible)
CREATE USER IF NOT EXISTS 'app_user'@'%' IDENTIFIED BY 'helloworld';

-- Grant privileges to app_user from any host
GRANT ALL PRIVILEGES ON app_db.* TO 'app_user'@'%';

-- Also grant privileges for localhost connections
GRANT ALL PRIVILEGES ON app_db.* TO 'app_user'@'localhost';

-- Flush privileges to ensure changes take effect
FLUSH PRIVILEGES;
