CREATE TABLE IF NOT EXISTS `wa_pending_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` varchar(100) NOT NULL,
  `image_url` text NOT NULL,
  `sender_jid` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
