-- KDesigns schema — utf8mb4, Phase 1
-- Do not store secrets here (no passwords, API keys, or seed data).

CREATE DATABASE IF NOT EXISTS `kdesigns`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kdesigns`;

-- ---------------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100)     NOT NULL,
  `email`         VARCHAR(254)     NOT NULL,
  `password_hash` VARCHAR(255)     NOT NULL,
  `role`          ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` DATETIME         NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- products
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(160) NOT NULL,
  `slug`        VARCHAR(180) NOT NULL,
  `category`    ENUM('fresh', 'dried', 'bloombox', 'glassdome', 'others') NOT NULL,
  `description` TEXT         NULL,
  `price_php`   INT UNSIGNED NOT NULL,
  `image_path`  VARCHAR(255) NOT NULL,
  `stock`       INT UNSIGNED NOT NULL DEFAULT 0,
  `badge`       VARCHAR(32)  NULL,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_slug` (`slug`),
  KEY `idx_products_category` (`category`),
  KEY `idx_products_active_stock` (`is_active`, `stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- orders
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `public_code`        VARCHAR(32)  NOT NULL,
  `user_id`            INT UNSIGNED NOT NULL,
  `fulfillment`        ENUM('pickup', 'delivery') NOT NULL DEFAULT 'pickup',
  `payment_method`     ENUM('Pay at Shop', 'GCash', 'BDO', 'BPI') NOT NULL,
  `receipt_ref`        VARCHAR(32)  NULL,
  `payment_received_at` DATETIME    NULL,
  `status`             ENUM('pending', 'processing', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  `date_needed`        DATE         NOT NULL,
  `time_needed`        VARCHAR(32)  NULL,
  `customer_name`      VARCHAR(100) NOT NULL,
  `customer_contact`   VARCHAR(32)  NOT NULL,
  `delivery_receiver`  VARCHAR(100) NULL,
  `delivery_contact`   VARCHAR(32)  NULL,
  `delivery_location`  TEXT         NULL,
  `total_php`          INT UNSIGNED NOT NULL,
  `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_public_code` (`public_code`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_created_at` (`created_at`),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- order_items
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`                 INT UNSIGNED NOT NULL,
  `product_id`               INT UNSIGNED NOT NULL,
  `product_name_snapshot`    VARCHAR(160) NOT NULL,
  `unit_price_php_snapshot`  INT UNSIGNED NOT NULL,
  `image_path_snapshot`      VARCHAR(255) NULL,
  `qty`                      INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `idx_order_items_product_id` (`product_id`),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_order_items_qty` CHECK (`qty` >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
