DROP TABLE IF EXISTS `notes`;

CREATE TABLE IF NOT EXISTS `notes` (
  `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `body` TEXT NOT NULL,
  `contact_id` INTEGER NOT NULL
);
