CREATE USER 'msqur'@'%' IDENTIFIED BY '';GRANT USAGE ON *.* TO 'msqur'@'%' IDENTIFIED BY '' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;CREATE DATABASE IF NOT EXISTS `msqur`;GRANT ALL PRIVILEGES ON `msqur`.* TO 'msqur'@'%';
