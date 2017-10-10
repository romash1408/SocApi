SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `socapi_post` (
  `ai` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `id` varchar(50) NOT NULL,
  `type` varchar(4) NOT NULL,
  `image` varchar(300) NOT NULL,
  `info` text CHARACTER SET utf8mb4 NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT '- system -',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_url` varchar(300) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - waits for moderation; 1 - confirmed; 2 - unconfirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `socapi_post`
  ADD UNIQUE KEY `id` (`id`,`type`),
  ADD UNIQUE KEY `ai` (`ai`),
  ADD KEY `date` (`date`);

CREATE TABLE `socapi_like` (
  `post_ai` int(11) NOT NULL,
  `user` varchar(30) NOT NULL,
  `liked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `socapi_like`
  ADD PRIMARY KEY (`post_ai`,`user`),
  ADD KEY `post_ai` (`post_ai`),
  ADD KEY `user` (`user`);

COMMIT;
