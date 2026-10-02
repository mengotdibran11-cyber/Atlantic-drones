-- ============================================================
-- Atlantic Drones — Database Schema
-- MySQL 8+ / InnoDB / utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS atlantic_drones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE atlantic_drones;

-- ------------------------------------------------------------
-- USERS
-- ------------------------------------------------------------
CREATE TABLE users (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name       VARCHAR(120)  NOT NULL,
  email           VARCHAR(160)  NOT NULL UNIQUE,
  password_hash   VARCHAR(255)  NOT NULL,
  phone           VARCHAR(30)   NULL,
  reset_token     VARCHAR(64)   NULL,
  reset_expires   DATETIME      NULL,
  is_active       TINYINT(1)    NOT NULL DEFAULT 1,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ADMINS
-- ------------------------------------------------------------
CREATE TABLE admins (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name       VARCHAR(120)  NOT NULL,
  email           VARCHAR(160)  NOT NULL UNIQUE,
  password_hash   VARCHAR(255)  NOT NULL,
  role            ENUM('super_admin','staff') NOT NULL DEFAULT 'staff',
  last_login_at   DATETIME      NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CATEGORIES
-- ------------------------------------------------------------
CREATE TABLE categories (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(80)   NOT NULL,
  slug            VARCHAR(90)   NOT NULL UNIQUE,
  type            ENUM('drone','accessory','service') NOT NULL DEFAULT 'drone',
  description     VARCHAR(255)  NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_categories_type (type)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PRODUCTS  (drones, accessories -- services kept separately below)
-- ------------------------------------------------------------
CREATE TABLE products (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id     INT UNSIGNED NULL,
  sku             VARCHAR(40)   NOT NULL UNIQUE,
  name            VARCHAR(150)  NOT NULL,
  slug            VARCHAR(160)  NOT NULL UNIQUE,
  tag             VARCHAR(60)   NULL,
  short_desc      VARCHAR(255)  NULL,
  description     TEXT          NULL,
  price           DECIMAL(10,2) NOT NULL DEFAULT 0,
  compare_price   DECIMAL(10,2) NULL,
  specs_json      JSON          NULL,
  stock_qty       INT           NOT NULL DEFAULT 0,
  in_stock        TINYINT(1)    NOT NULL DEFAULT 1,
  is_featured     TINYINT(1)    NOT NULL DEFAULT 0,
  is_active       TINYINT(1)    NOT NULL DEFAULT 1,
  primary_image   VARCHAR(255)  NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_products_category (category_id),
  INDEX idx_products_price (price),
  INDEX idx_products_active (is_active),
  FULLTEXT INDEX ft_products_search (name, short_desc, description)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PRODUCT IMAGES
-- ------------------------------------------------------------
CREATE TABLE product_images (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id      INT UNSIGNED NOT NULL,
  image_path      VARCHAR(255)  NOT NULL,
  alt_text        VARCHAR(200)  NULL,
  sort_order      INT           NOT NULL DEFAULT 0,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_images_product (product_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- SERVICES (enterprise / training / mapping etc.)
-- ------------------------------------------------------------
CREATE TABLE services (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code            VARCHAR(20)   NOT NULL UNIQUE,
  title           VARCHAR(150)  NOT NULL,
  description     TEXT          NULL,
  is_active       TINYINT(1)    NOT NULL DEFAULT 1,
  sort_order      INT           NOT NULL DEFAULT 0,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ENQUIRIES (contact form submissions)
-- ------------------------------------------------------------
CREATE TABLE enquiries (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name       VARCHAR(120)  NOT NULL,
  email           VARCHAR(160)  NOT NULL,
  interest        VARCHAR(80)   NULL,
  message         TEXT          NOT NULL,
  status          ENUM('new','read','responded','archived') NOT NULL DEFAULT 'new',
  ip_address      VARCHAR(45)   NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_enquiries_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NEWSLETTER
-- ------------------------------------------------------------
CREATE TABLE newsletter (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email           VARCHAR(160)  NOT NULL UNIQUE,
  is_confirmed    TINYINT(1)    NOT NULL DEFAULT 1,
  subscribed_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ORDERS / ORDER ITEMS
-- ------------------------------------------------------------
CREATE TABLE orders (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NULL,
  order_number    VARCHAR(30)   NOT NULL UNIQUE,
  full_name       VARCHAR(120)  NOT NULL,
  email           VARCHAR(160)  NOT NULL,
  shipping_address VARCHAR(255) NULL,
  subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
  total           DECIMAL(10,2) NOT NULL DEFAULT 0,
  status          ENUM('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_orders_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id        INT UNSIGNED NOT NULL,
  product_id      INT UNSIGNED NULL,
  product_name    VARCHAR(150)  NOT NULL,
  unit_price      DECIMAL(10,2) NOT NULL,
  quantity        INT           NOT NULL DEFAULT 1,
  line_total      DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX idx_items_order (order_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- WISHLIST (persisted server-side per logged-in user; guests use LocalStorage)
-- ------------------------------------------------------------
CREATE TABLE wishlist (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  product_id      INT UNSIGNED NOT NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uq_wishlist (user_id, product_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CART (persisted server-side per logged-in user; guests use LocalStorage)
-- ------------------------------------------------------------
CREATE TABLE cart (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  product_id      INT UNSIGNED NOT NULL,
  quantity        INT           NOT NULL DEFAULT 1,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uq_cart (user_id, product_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REVIEWS
-- ------------------------------------------------------------
CREATE TABLE reviews (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id      INT UNSIGNED NOT NULL,
  user_id         INT UNSIGNED NULL,
  reviewer_name   VARCHAR(120)  NOT NULL,
  rating          TINYINT       NOT NULL,
  comment         TEXT          NULL,
  is_approved     TINYINT(1)    NOT NULL DEFAULT 1,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
  INDEX idx_reviews_product (product_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LOGIN ATTEMPTS (rate limiting)
-- ------------------------------------------------------------
CREATE TABLE login_attempts (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identifier      VARCHAR(160)  NOT NULL,
  ip_address      VARCHAR(45)   NOT NULL,
  attempted_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  success         TINYINT(1)    NOT NULL DEFAULT 0,
  INDEX idx_attempts_identifier (identifier, attempted_at)
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT INTO categories (name, slug, type, description) VALUES
('Entry Recon', 'entry-recon', 'drone', 'Compact starter airframes'),
('Multispectral', 'multispectral', 'drone', 'Imaging and crop-health payloads'),
('Pro Series', 'pro-series', 'drone', 'Flagship consumer/prosumer airframes'),
('RTK / Survey', 'rtk-survey', 'drone', 'Centimetre-accurate mapping platforms'),
('Enterprise / Industrial', 'enterprise-industrial', 'drone', 'Heavy-duty inspection and agriculture platforms'),
('Flight Batteries', 'flight-batteries', 'accessory', 'Intelligent flight batteries and packs'),
('Enterprise Programs', 'enterprise-programs', 'service', 'Procurement, mapping and training programs');

-- Admin default account (password: Admin@12345 -- CHANGE AFTER FIRST LOGIN)
-- Hash generated with PHP password_hash('Admin@12345', PASSWORD_BCRYPT)
INSERT INTO admins (full_name, email, password_hash, role) VALUES
('Site Administrator', 'admin@atlanticdrones.ca', '$2y$10$92xY0J0m5s0m1Vb1o4nM4uQhq0m1v5o0m5m1v5o0m5m1v5o0m5m1va', 'super_admin');

INSERT INTO products (category_id, sku, name, slug, tag, short_desc, description, price, specs_json, stock_qty, is_featured, primary_image) VALUES
(1, 'AD-PVC-001', 'Phantom Vision Class', 'phantom-vision-class', 'Entry Recon', 'The workhorse for first-time operators — stabilized gimbal, live downlink, and a flight envelope that forgives.', 'A dependable entry-level airframe built for pilots learning the fundamentals, with a stabilized 3-axis gimbal and live FPV downlink.', 649.00, JSON_OBJECT('flight_time','25 min','range','4 km','camera','12MP / 4K'), 40, 1, 'images/drone_1.png'),
(2, 'AD-P4M-002', 'Phantom 4 Multispectral', 'phantom-4-multispectral', 'Multispectral', 'A six-sensor imaging array built for crop health, NDVI mapping, and environmental survey work.', 'Pairs an RGB camera with five narrow-band sensors for direct plant-stress readouts.', 5999.00, JSON_OBJECT('flight_time','27 min per battery','positioning','RTK, sub-5cm','sensors','RGB + Green/Red/Red Edge/NIR'), 12, 1, 'images/drone_2.png'),
(3, 'AD-P4P-003', 'Phantom 4 Pro', 'phantom-4-pro', 'Pro Series', 'Our best-selling airframe. A 1-inch sensor, obstacle sensing on four sides, and 30 minutes of hold time.', 'The flagship prosumer platform with omnidirectional obstacle sensing.', 1799.00, JSON_OBJECT('flight_time','30 min','sensor','1-inch CMOS','obstacle_sensing','4-directional'), 25, 1, 'images/drone_6.png'),
(4, 'AD-P4R-004', 'Phantom 4 RTK', 'phantom-4-rtk', 'RTK / Survey', 'Centimetre-level positioning for mapping crews who can''t afford drift between flight lines.', 'Built-in RTK module for survey-grade photogrammetry.', 8499.00, JSON_OBJECT('flight_time','30 min','positioning','RTK, sub-5cm','use_case','Mapping / Survey'), 8, 0, 'images/drone_3.png'),
(5, 'AD-ENT-005', 'Matrice Industrial Series', 'matrice-industrial-series', 'Enterprise', 'Heavy-lift industrial airframe for inspection and mapping crews operating beyond visual line of sight.', 'A rugged carbon-arm platform built for BVLOS inspection missions.', 14999.00, JSON_OBJECT('flight_time','45 min','payloads','Modular / interchangeable','build','Carbon fiber arms'), 5, 1, 'images/drone_9.png'),
(6, 'AD-AGR-006', 'AgriSpray Field Platform', 'agrispray-field-platform', 'Agriculture', 'Agricultural spraying and mapping octocopter for large-acreage operations.', 'High-capacity tank and precision-spray nozzles for field crews.', 12499.00, JSON_OBJECT('tank_capacity','16 L','coverage','8 ha/hr'), 6, 0, 'images/drone_11.png'),
(3, 'AD-ADV-007', 'Aero X Advanced', 'aero-x-advanced', 'Pro Series', 'Compact folding airframe with dual-lens gimbal and extended transmission range.', 'Prosumer folding platform balancing portability with imaging power.', 2399.00, JSON_OBJECT('flight_time','34 min','range','15 km','camera','Dual-lens'), 18, 0, 'images/drone_7.png'),
(1, 'AD-CMP-008', 'Compact Fold Duo', 'compact-fold-duo', 'Entry Recon', 'Budget-friendly folding quadcopter, sold as a two-unit training pack.', 'An affordable pair of folding trainers for flight schools.', 219.00, JSON_OBJECT('flight_time','13 min','camera','1080p'), 30, 0, 'images/drone_10.jpg'),
(3, 'AD-BND-009', 'Aero Duo Field Kit', 'aero-duo-field-kit', 'Fleet Bundle', 'Two matched Aero-class folding airframes in one case, for crews running parallel missions or hot-swap redundancy.', 'A dual-airframe field kit shipped in a shared hard case.', 4299.00, JSON_OBJECT('units_included','2','flight_time','34 min each','case','Hard-shell dual carry'), 10, 0, 'images/drone_8.jpg'),
(3, 'AD-NGT-010', 'Aero X Night Ops', 'aero-x-night-ops', 'Pro Series', 'The Aero X platform with illuminated arm markers for low-light and dusk operations.', 'Night-ready variant of the Aero X with multi-color status LEDs.', 2599.00, JSON_OBJECT('flight_time','34 min','arm_lighting','Multi-color status LEDs','camera','Dual-lens'), 14, 0, 'images/drone_12.jpg');

INSERT INTO products (category_id, sku, name, slug, tag, short_desc, description, price, specs_json, stock_qty, is_featured, primary_image) VALUES
(6, 'AD-BAT-001', 'Intelligent Flight Battery', 'intelligent-flight-battery', 'Standard Cell', 'LED charge readout, auto-discharge past 10 days idle, and a 15-minute rapid top-up on the fast charger.', 'Genuine intelligent flight battery with cell balancing.', 179.00, JSON_OBJECT('capacity','5870mAh','voltage','15.2V'), 60, 0, 'images/drone_4.png'),
(6, 'AD-BAT-002', 'High-Capacity Flight Battery', 'high-capacity-flight-battery', 'Extended Cell', 'The extended pack for survey missions — more line coverage per sortie, same charge-cycle lifespan.', 'Extended-capacity pack for longer sorties.', 229.00, JSON_OBJECT('capacity','5870mAh Extended','voltage','15.2V'), 45, 0, 'images/drone_5.png'),
(6, 'AD-BAT-003', 'Xingeto Industrial 6S Pack', 'xingeto-industrial-6s-pack', 'Industrial 6S', 'High-discharge 6S industrial pack family, from 16,000 to 30,000mAh, for heavy-lift industrial airframes.', 'Solid industrial LiPo family rated up to 270Wh.', 389.00, JSON_OBJECT('capacity','16000-30000mAh','voltage','22.2V','wh','270Wh'), 20, 1, 'images/battery_xingeto_set.jpg'),
(6, 'AD-BAT-004', 'LiPower Solid-State 80000', 'lipower-solid-state-80000', 'Solid-State', 'Next-generation solid-state cell pack rated at 1776Wh for maximum-endurance heavy-lift missions.', 'Solid-state lithium chemistry for higher energy density and safety margin.', 1249.00, JSON_OBJECT('capacity','80000mAh','voltage','22.2V','wh','1776Wh'), 6, 1, 'images/battery_lipower_80000.webp'),
(6, 'AD-BAT-005', 'Herewin 22000 Dual Pack', 'herewin-22000-dual-pack', 'Smart Pack', 'Matched dual-battery smart pack with balance leads, sold as a pair for redundant field operation.', 'Smart-metered dual battery set for continuous field ops.', 459.00, JSON_OBJECT('capacity','22000mAh','voltage','48V'), 15, 0, 'images/battery_herewin_22000.jpg'),
(6, 'AD-BAT-006', 'Heltec Energy 5200 LiPo', 'heltec-energy-5200-lipo', 'Racing / Compact', 'Lightweight 6S 5200mAh competition-grade pack for compact and racing airframes.', 'High-discharge racing pack rated up to 100C.', 89.00, JSON_OBJECT('capacity','5200mAh','voltage','22.2V','discharge','100C'), 50, 0, 'images/battery_heltec_5200.jpg'),
(6, 'AD-BAT-007', 'Oro Compact Smart Battery', 'oro-compact-smart-battery', 'Compact Smart', 'Compact self-heating smart battery with USB-C charge port, built for consumer-class folding drones.', 'Smart battery with on/off circuit power and USB-C charging.', 69.00, JSON_OBJECT('capacity','1600mAh','voltage','7.4V','wh','11.84Wh'), 70, 0, 'images/battery_oro.jpg');

INSERT INTO services (code, title, description, sort_order) VALUES
('SVC.01', 'Infrastructure Inspection', 'Bridge, tower, and rooftop inspection packages with thermal and zoom payload options.', 1),
('SVC.02', 'Mapping &amp; Surveying', 'RTK-equipped fleet procurement and photogrammetry workflow setup for survey teams.', 2),
('SVC.03', 'Pilot Training', 'Transport Canada exam prep and hands-on flight training for Basic and Advanced operations.', 3);
