CREATE DATABASE doingsdone DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;
USE `doingsdone`;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  email CHAR(100) UNIQUE NOT NULL,
  name CHAR(100) NOT NULL,
  password CHAR NOT NULL
);
CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title CHAR(100) NOT NULL,
  user_id INT NOT NULL
);
CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status TINYINT DEFAULT 0,
  title CHAR(255) NOT NULL,
  url_file CHAR(255),
  deadline TIMESTAMP,
  user_id INT NOT NULL,
  project_id INT
);
CREATE INDEX u_mail ON users(email);
CREATE INDEX t_text ON tasks(title);
