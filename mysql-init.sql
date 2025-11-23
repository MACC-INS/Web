-- init.sql.template
-- Create application-specific user with limited privileges
CREATE USER IF NOT EXISTS 'app_user'@'%' IDENTIFIED BY 'StrongPassword123!';
GRANT SELECT, INSERT, UPDATE, DELETE ON flights.* TO 'app_user'@'%';

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove remote root access (keep localhost only)
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Remove test database
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';

-- Apply changes
FLUSH PRIVILEGES;