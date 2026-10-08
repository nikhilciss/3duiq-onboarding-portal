-- ============================================================
-- 3duiq Onboarding Portal - LCR (Least Cost Routing) Database Schema
-- File: lcr.sql
-- Description: Routing rules, carriers, rate tables, call routing
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ------------------------------------------------------------
-- Table: carriers
-- Telecom carrier/trunk provider definitions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `carriers` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(200)    NOT NULL,
    `code`          VARCHAR(50)     NOT NULL UNIQUE,
    `type`          ENUM('sip','pri','pstn','voip') NOT NULL DEFAULT 'sip',
    `host`          VARCHAR(255)    NOT NULL COMMENT 'SIP server hostname or IP',
    `port`          SMALLINT UNSIGNED NOT NULL DEFAULT 5060,
    `username`      VARCHAR(100)    DEFAULT NULL,
    `password`      VARCHAR(255)    DEFAULT NULL,
    `realm`         VARCHAR(255)    DEFAULT NULL,
    `auth_type`     ENUM('none','register','digest') NOT NULL DEFAULT 'digest',
    `codec_priority` VARCHAR(200)   DEFAULT 'PCMU,PCMA,G729',
    `max_channels`  SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    `active_channels` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `priority`      TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Lower = higher priority',
    `weight`        TINYINT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'For load balancing (1-100)',
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `notes`         TEXT            DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Telecom carrier/trunk providers';

-- ------------------------------------------------------------
-- Table: rate_tables
-- Rate table groupings per carrier or destination
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_tables` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(200)    NOT NULL,
    `description` TEXT            DEFAULT NULL,
    `carrier_id`  BIGINT UNSIGNED DEFAULT NULL,
    `currency`    CHAR(3)         NOT NULL DEFAULT 'USD',
    `effective_date` DATE         NOT NULL DEFAULT (CURRENT_DATE),
    `expiry_date` DATE            DEFAULT NULL,
    `is_default`  TINYINT(1)      NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_carrier_id` (`carrier_id`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_effective_date` (`effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rate table groupings';

-- ------------------------------------------------------------
-- Table: rates
-- Per-prefix call rate definitions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rates` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rate_table_id`   BIGINT UNSIGNED NOT NULL,
    `carrier_id`      BIGINT UNSIGNED NOT NULL,
    `prefix`          VARCHAR(20)     NOT NULL COMMENT 'Destination prefix (e.g. 1, 44, 9180)',
    `destination`     VARCHAR(200)    NOT NULL COMMENT 'Human readable destination name',
    `country_code`    CHAR(2)         DEFAULT NULL COMMENT 'ISO 3166 country code',
    `rate_per_minute` DECIMAL(10,6)   NOT NULL DEFAULT 0.000000,
    `connection_fee`  DECIMAL(10,6)   NOT NULL DEFAULT 0.000000,
    `billing_increment` SMALLINT UNSIGNED NOT NULL DEFAULT 6 COMMENT 'Billing increment in seconds',
    `min_duration`    SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Minimum chargeable seconds',
    `effective_date`  DATE            NOT NULL DEFAULT (CURRENT_DATE),
    `expiry_date`     DATE            DEFAULT NULL,
    `is_active`       TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_rate_table_id` (`rate_table_id`),
    INDEX `idx_carrier_id` (`carrier_id`),
    INDEX `idx_prefix` (`prefix`),
    INDEX `idx_country_code` (`country_code`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Per-prefix call rates';

-- ------------------------------------------------------------
-- Table: routing_rules
-- LCR routing decision rules (prefix-based priority routing)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `routing_rules` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(200)    NOT NULL,
    `prefix`        VARCHAR(20)     NOT NULL COMMENT 'Destination number prefix to match',
    `carrier_id`    BIGINT UNSIGNED NOT NULL,
    `rate_table_id` BIGINT UNSIGNED DEFAULT NULL,
    `priority`      TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Lower = try first',
    `weight`        TINYINT UNSIGNED NOT NULL DEFAULT 100,
    `max_cost_per_min` DECIMAL(8,4) DEFAULT NULL COMMENT 'Cost ceiling per minute',
    `time_restriction` JSON         DEFAULT NULL COMMENT 'Time-based routing restrictions',
    `failover_rule_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Fallback rule if this fails',
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_prefix` (`prefix`),
    INDEX `idx_carrier_id` (`carrier_id`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='LCR routing rules';

-- ------------------------------------------------------------
-- Table: routing_logs
-- Log of every routing decision made
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `routing_logs` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `call_uuid`       VARCHAR(100)    NOT NULL,
    `destination`     VARCHAR(20)     NOT NULL,
    `matched_prefix`  VARCHAR(20)     DEFAULT NULL,
    `selected_carrier_id` BIGINT UNSIGNED DEFAULT NULL,
    `rule_id`         BIGINT UNSIGNED DEFAULT NULL,
    `rate_per_minute` DECIMAL(10,6)   DEFAULT NULL,
    `routing_attempts` JSON           DEFAULT NULL COMMENT 'List of carriers tried',
    `final_status`    ENUM('routed','failed','no_route') NOT NULL DEFAULT 'routed',
    `routed_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_call_uuid` (`call_uuid`),
    INDEX `idx_destination` (`destination`),
    INDEX `idx_routed_at` (`routed_at`),
    INDEX `idx_final_status` (`final_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log of routing decisions';

-- ------------------------------------------------------------
-- Sample Data
-- ------------------------------------------------------------
INSERT INTO `carriers` (`name`, `code`, `type`, `host`, `port`, `username`, `max_channels`, `priority`, `weight`, `is_active`) VALUES
('Twilio Voice',    'TWILIO',    'sip',  'pstn.twilio.com',        5060, 'twilio_acct',   50,  1, 100, 1),
('Vonage Business', 'VONAGE',    'sip',  'sip.nexmo.com',          5060, 'vonage_user',   30,  2, 80,  1),
('AWS Connect',     'AWSCONN',   'voip', 'connect.us-east-1.aws',  5080, 'aws_connect',   100, 3, 60,  1),
('PSTN Backup',     'PSTN_BCK',  'pstn', 'backup.carrier.com',     5060, NULL,            20,  4, 40,  0);

INSERT INTO `rate_tables` (`name`, `description`, `carrier_id`, `currency`, `effective_date`, `is_default`, `is_active`) VALUES
('Twilio US Rates',   'US domestic rates via Twilio',      1, 'USD', '2026-01-01', 1, 1),
('Vonage Intl Rates', 'International rates via Vonage',    2, 'USD', '2026-01-01', 0, 1),
('AWS Fallback',      'Fallback rates via AWS Connect',    3, 'USD', '2026-01-01', 0, 1);

INSERT INTO `rates` (`rate_table_id`, `carrier_id`, `prefix`, `destination`, `country_code`, `rate_per_minute`, `connection_fee`, `billing_increment`) VALUES
(1, 1, '1',     'USA & Canada',    'US', 0.004000, 0.000000, 6),
(1, 1, '1800',  'USA Toll Free',   'US', 0.000000, 0.000000, 1),
(1, 1, '1900',  'USA Premium',     'US', 0.500000, 0.100000, 60),
(2, 2, '44',    'United Kingdom',  'GB', 0.012000, 0.001000, 6),
(2, 2, '91',    'India',           'IN', 0.009500, 0.000500, 6),
(2, 2, '61',    'Australia',       'AU', 0.018000, 0.001500, 6),
(3, 3, '1',     'USA Fallback',    'US', 0.006000, 0.000000, 6);

INSERT INTO `routing_rules` (`name`, `prefix`, `carrier_id`, `rate_table_id`, `priority`, `weight`, `is_active`) VALUES
('US Primary Route',  '1',    1, 1, 1, 100, 1),
('US Secondary',      '1',    3, 3, 2, 80,  1),
('UK Route',          '44',   2, 2, 1, 100, 1),
('India Route',       '91',   2, 2, 1, 100, 1),
('Australia Route',   '61',   2, 2, 1, 100, 1),
('Toll Free Route',   '1800', 1, 1, 1, 100, 1);
