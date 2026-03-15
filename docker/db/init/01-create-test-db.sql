CREATE DATABASE IF NOT EXISTS payment_system_test
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON payment_system_test.* TO 'admin'@'%';
FLUSH PRIVILEGES;