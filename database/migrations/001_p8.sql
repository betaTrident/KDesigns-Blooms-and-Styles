ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `receipt_ref` VARCHAR(32) NULL AFTER `payment_method`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `payment_received_at` DATETIME NULL AFTER `receipt_ref`;
