#!/bin/bash
set -e

# Generate SQL file using ENV variables
cat <<EOF > /docker-entrypoint-initdb.d/init-app.sql
-- Install validate password component if available
INSTALL COMPONENT 'file://component_validate_password';

-- Set strict password policy
SET GLOBAL validate_password.policy = STRONG;
SET GLOBAL validate_password.length = 10;
SET GLOBAL validate_password.mixed_case_count = 1;
SET GLOBAL validate_password.number_count = 2;
SET GLOBAL validate_password.special_char_count = 1;

-- Create user with explicit password requirements
CREATE USER IF NOT EXISTS '${MYSQL_APP_USER}'@'%' 
IDENTIFIED BY '${MYSQL_APP_PASSWORD}'
REQUIRE 
    LENGTH >= 10
    PASSWORD HISTORY 5
    PASSWORD REUSE INTERVAL 365 DAY
    PASSWORD REQUIRE CURRENT DEFAULT;

-- Apply custom regex validation for specific pattern (starts with 2 numbers)
ALTER USER '${MYSQL_APP_USER}'@'%' REQUIRE
    PASSWORD REGEXP '^[0-9]{2,}.*[A-Z].*[a-z].*[^a-zA-Z0-9]|.*[A-Z].*[a-z].*[^a-zA-Z0-9].*[0-9]{2,}';

GRANT SELECT, INSERT, UPDATE, DELETE ON flights.* TO '${MYSQL_APP_USER}'@'%';

-- Set password expiration policy
ALTER USER '${MYSQL_APP_USER}'@'%' PASSWORD EXPIRE INTERVAL 90 DAY;

-- Clean up default users
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%';

FLUSH PRIVILEGES;
EOF

echo "[INFO] Generated init-app.sql with strict password policies"

# → Continue normal MySQL startup
exec docker-entrypoint.sh mysqld