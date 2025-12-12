-- MySQL initialization script
-- This script ensures that the app_user can connect from any host within the Docker network

-- Grant privileges to app_user from any host (user is already created by Docker)
GRANT ALL PRIVILEGES ON app_db.* TO 'app_user'@'%';

-- Flush privileges to ensure changes take effect
FLUSH PRIVILEGES;
