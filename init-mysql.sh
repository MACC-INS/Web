#!/bin/bash
set -e

# Generate SQL file using ENV variables
cat <<EOF > /docker-entrypoint-initdb.d/init-app.sql
CREATE USER IF NOT EXISTS '${MYSQL_APP_USER}'@'%' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';
GRANT SELECT, INSERT, UPDATE, DELETE ON flights.* TO '${MYSQL_APP_USER}'@'%';

DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%';

FLUSH PRIVILEGES;
EOF

echo "[INFO] Generated init-app.sql with app user: $MYSQL_APP_USER"

# → Continue normal MySQL startup
exec docker-entrypoint.sh mysqld
