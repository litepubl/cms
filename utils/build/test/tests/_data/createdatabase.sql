CREATE DATABASE jusoft_test;
CREATE USER jusoft_test WITH password 'test';
GRANT ALL privileges ON DATABASE jusoft_test TO jusoft_test;
GRANT ALL PRIVILEGES ON jusoft_test.* TO jusoft_test@localhost IDENTIFIED BY 'test';