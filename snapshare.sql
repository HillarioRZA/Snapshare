/*
SQLyog Community v13.2.0 (64 bit)
MySQL - 10.4.32-MariaDB : Database - snapshare
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`snapshare` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `snapshare`;

/*Table structure for table `comments` */

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`comment_id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `comments` */

insert  into `comments`(`comment_id`,`post_id`,`user_id`,`comment`,`created_at`) values 
(1,6,1,'abcd','2024-06-24 02:42:52'),
(2,6,1,'efgh','2024-06-24 02:42:57'),
(3,6,1,'aaa','2024-06-24 02:55:19'),
(4,6,1,'asada','2024-06-24 02:56:32'),
(5,6,4,'aaaa','2024-06-24 03:04:29');

/*Table structure for table `filters` */

DROP TABLE IF EXISTS `filters`;

CREATE TABLE `filters` (
  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_name` varchar(50) NOT NULL,
  `filter_description` text DEFAULT NULL,
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `filters` */

/*Table structure for table `followers` */

DROP TABLE IF EXISTS `followers`;

CREATE TABLE `followers` (
  `follower_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `follower_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`follower_id`),
  KEY `user_id` (`user_id`),
  KEY `follower_user_id` (`follower_user_id`),
  CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`follower_user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `followers` */

insert  into `followers`(`follower_id`,`user_id`,`follower_user_id`,`created_at`) values 
(24,1,4,'2024-06-24 16:10:37'),
(32,4,1,'2024-06-24 16:19:46');

/*Table structure for table `likes` */

DROP TABLE IF EXISTS `likes`;

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`like_id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`),
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `likes` */

insert  into `likes`(`like_id`,`post_id`,`user_id`,`created_at`) values 
(10,6,1,'2024-06-24 02:56:29'),
(11,6,4,'2024-06-24 03:04:26'),
(13,7,4,'2024-06-24 14:40:25');

/*Table structure for table `posts` */

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `filter_id` int(11) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `location_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `filter_id` (`filter_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`filter_id`) REFERENCES `filters` (`filter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `posts` */

insert  into `posts`(`post_id`,`user_id`,`image_url`,`filter_id`,`caption`,`location_url`,`created_at`) values 
(6,1,'images.png',NULL,'wqeqeqe','https://maps.app.goo.gl/an3qkLLAV4MKTm1i7','2024-06-24 02:35:00'),
(7,4,'test.png',NULL,'12345','https://maps.app.goo.gl/an3qkLLAV4MKTm1i7','2024-06-24 14:29:43'),
(8,4,'images.jpg',NULL,'asdadas','https://maps.app.goo.gl/an3qkLLAV4MKTm1i7','2024-06-24 14:45:49'),
(9,4,'gunting.png',NULL,'asdadacxz','https://maps.app.goo.gl/an3qkLLAV4MKTm1i7','2024-06-24 14:50:23');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`user_id`,`username`,`email`,`password`,`profile_picture`,`created_at`) values 
(1,'lynn','test@gmail.com','abcd1234','https://images.pexels.com/photos/45201/kitty-cat-kitten-pet-45201.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1','2024-06-23 23:05:56'),
(2,'abcd','abcd@gmail.com','abcd321',NULL,'2024-06-23 23:58:01'),
(3,'test12314','gandhi@yahoo.com','abcd',NULL,'2024-06-24 00:21:46'),
(4,'reza','qwerty@gmail.com','abcd1234',NULL,'2024-06-24 00:22:42'),
(5,'reza312','reza@gmail.com','abcd',NULL,'2024-06-24 00:23:29');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
