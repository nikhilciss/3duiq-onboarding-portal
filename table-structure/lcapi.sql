-- ============================================================
-- 3duiq Onboarding Portal - LCAPI (Lead/Customer API) Database Schema
-- File: lcapi.sql
-- Description: Leads, customers, interactions, API integrations
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ------------------------------------------------------------
-- Table: leads
-- Inbound and outbound sales/contact leads
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_uuid`       VARCHAR(100)    NOT NULL UNIQUE,
    `source`          VARCHAR(100)    DEFAULT NULL COMMENT 'Lead source: web, api, import, referral',
    `campaign_ref`    VARCHAR(100)    DEFAULT NULL,
    `first_name`      VARCHAR(100)    NOT NULL,
    `last_name`       VARCHAR(100)    DEFAULT NULL,
    `email`           VARCHAR(191)    DEFAULT NULL,
    `phone_primary`   VARCHAR(20)     NOT NULL,
    `phone_secondary` VARCHAR(20)     DEFAULT NULL,
    `phone_mobile`    VARCHAR(20)     DEFAULT NULL,
    `company_name`    VARCHAR(200)    DEFAULT NULL,
    `job_title`       VARCHAR(100)    DEFAULT NULL,
    `country`         CHAR(2)         DEFAULT NULL COMMENT 'ISO 3166 code',
    `state`           VARCHAR(100)    DEFAULT NULL,
    `city`            VARCHAR(100)    DEFAULT NULL,
    `zip_code`        VARCHAR(20)     DEFAULT NULL,
    `address`         TEXT            DEFAULT NULL,
    `timezone`        VARCHAR(60)     DEFAULT 'UTC',
    `language`        VARCHAR(10)     DEFAULT 'en',
    `status`          ENUM('new','assigned','contacted','qualified','unqualified','converted','do_not_call','invalid') NOT NULL DEFAULT 'new',
    `priority`        ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `assigned_agent_id` BIGINT UNSIGNED DEFAULT NULL,
    `score`           TINYINT UNSIGNED DEFAULT NULL COMMENT 'Lead score 0-100',
    `opt_in_email`    TINYINT(1)      NOT NULL DEFAULT 0,
    `opt_in_sms`      TINYINT(1)      NOT NULL DEFAULT 0,
    `opt_in_call`     TINYINT(1)      NOT NULL DEFAULT 1,
    `do_not_contact_until` DATETIME   DEFAULT NULL,
    `tags`            JSON            DEFAULT NULL,
    `custom_fields`   JSON            DEFAULT NULL,
    `notes`           TEXT            DEFAULT NULL,
    `converted_at`    DATETIME        DEFAULT NULL,
    `last_contacted_at` DATETIME      DEFAULT NULL,
    `next_callback_at`  DATETIME      DEFAULT NULL,
    `attempt_count`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_assigned_agent_id` (`assigned_agent_id`),
    INDEX `idx_phone_primary` (`phone_primary`),
    INDEX `idx_email` (`email`),
    INDEX `idx_source` (`source`),
    INDEX `idx_next_callback_at` (`next_callback_at`),
    INDEX `idx_country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sales and contact leads';

-- ------------------------------------------------------------
-- Table: customers
-- Converted leads / existing customers
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_ref`     VARCHAR(100)    NOT NULL UNIQUE COMMENT 'CRM customer reference ID',
    `lead_id`          BIGINT UNSIGNED DEFAULT NULL COMMENT 'Original lead if converted',
    `account_type`     ENUM('individual','business') NOT NULL DEFAULT 'individual',
    `first_name`       VARCHAR(100)    NOT NULL,
    `last_name`        VARCHAR(100)    DEFAULT NULL,
    `company_name`     VARCHAR(200)    DEFAULT NULL,
    `email`            VARCHAR(191)    NOT NULL,
    `phone_primary`    VARCHAR(20)     NOT NULL,
    `phone_secondary`  VARCHAR(20)     DEFAULT NULL,
    `country`          CHAR(2)         DEFAULT NULL,
    `state`            VARCHAR(100)    DEFAULT NULL,
    `city`             VARCHAR(100)    DEFAULT NULL,
    `address`          TEXT            DEFAULT NULL,
    `zip_code`         VARCHAR(20)     DEFAULT NULL,
    `segment`          VARCHAR(100)    DEFAULT NULL COMMENT 'Customer segment e.g. SMB, Enterprise',
    `account_status`   ENUM('active','suspended','closed','pending') NOT NULL DEFAULT 'active',
    `lifetime_value`   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    `total_calls`      INT UNSIGNED    NOT NULL DEFAULT 0,
    `last_call_at`     DATETIME        DEFAULT NULL,
    `account_manager_id` BIGINT UNSIGNED DEFAULT NULL,
    `contract_start`   DATE            DEFAULT NULL,
    `contract_end`     DATE            DEFAULT NULL,
    `notes`            TEXT            DEFAULT NULL,
    `tags`             JSON            DEFAULT NULL,
    `custom_fields`    JSON            DEFAULT NULL,
    `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_account_status` (`account_status`),
    INDEX `idx_email` (`email`),
    INDEX `idx_phone_primary` (`phone_primary`),
    INDEX `idx_lead_id` (`lead_id`),
    INDEX `idx_segment` (`segment`),
    INDEX `idx_account_manager_id` (`account_manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer accounts';

-- ------------------------------------------------------------
-- Table: interactions
-- All touchpoints: calls, emails, chats, notes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `interactions` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_type`    ENUM('lead','customer') NOT NULL DEFAULT 'lead',
    `entity_id`      BIGINT UNSIGNED NOT NULL,
    `call_uuid`      VARCHAR(100)    DEFAULT NULL COMMENT 'Link to tracking.calls',
    `agent_id`       BIGINT UNSIGNED DEFAULT NULL,
    `type`           ENUM('call','email','sms','chat','meeting','note','callback_scheduled') NOT NULL,
    `direction`      ENUM('inbound','outbound','internal') DEFAULT NULL,
    `subject`        VARCHAR(300)    DEFAULT NULL,
    `body`           LONGTEXT        DEFAULT NULL,
    `outcome`        VARCHAR(100)    DEFAULT NULL COMMENT 'Result of interaction',
    `duration_seconds` INT UNSIGNED  DEFAULT NULL,
    `sentiment`      ENUM('positive','neutral','negative') DEFAULT 'neutral',
    `scheduled_at`   DATETIME        DEFAULT NULL,
    `occurred_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_agent_id` (`agent_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_call_uuid` (`call_uuid`),
    INDEX `idx_occurred_at` (`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='All customer/lead touchpoints and interactions';

-- ------------------------------------------------------------
-- Table: api_integrations
-- Third-party API webhook/integration settings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_integrations` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(200)    NOT NULL,
    `provider`     VARCHAR(100)    NOT NULL COMMENT 'e.g. salesforce, hubspot, zapier',
    `type`         ENUM('crm','marketing','helpdesk','payment','custom') NOT NULL DEFAULT 'custom',
    `endpoint_url` VARCHAR(500)    DEFAULT NULL,
    `api_key`      VARCHAR(500)    DEFAULT NULL,
    `api_secret`   VARCHAR(500)    DEFAULT NULL,
    `auth_type`    ENUM('none','api_key','oauth2','basic','bearer') NOT NULL DEFAULT 'api_key',
    `oauth_token`  TEXT            DEFAULT NULL,
    `oauth_refresh` TEXT           DEFAULT NULL,
    `token_expires_at` DATETIME    DEFAULT NULL,
    `webhook_secret`   VARCHAR(255) DEFAULT NULL,
    `event_triggers`   JSON        DEFAULT NULL COMMENT 'Events that trigger this integration',
    `field_mapping`    JSON        DEFAULT NULL COMMENT 'Field name mapping configuration',
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `last_sync_at` DATETIME        DEFAULT NULL,
    `sync_status`  ENUM('idle','running','success','failed') DEFAULT 'idle',
    `error_log`    TEXT            DEFAULT NULL,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_provider` (`provider`),
    INDEX `idx_type` (`type`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Third-party API and webhook integrations';

-- ------------------------------------------------------------
-- Table: api_logs
-- Log of every API call made to/from integrations
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_logs` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `integration_id`   BIGINT UNSIGNED DEFAULT NULL,
    `method`           ENUM('GET','POST','PUT','PATCH','DELETE','WEBHOOK') NOT NULL,
    `endpoint`         VARCHAR(500)    NOT NULL,
    `request_headers`  JSON            DEFAULT NULL,
    `request_body`     LONGTEXT        DEFAULT NULL,
    `response_code`    SMALLINT UNSIGNED DEFAULT NULL,
    `response_body`    LONGTEXT        DEFAULT NULL,
    `duration_ms`      INT UNSIGNED    DEFAULT NULL COMMENT 'Request duration in ms',
    `status`           ENUM('success','failed','timeout','error') NOT NULL DEFAULT 'success',
    `error_message`    TEXT            DEFAULT NULL,
    `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_integration_id` (`integration_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_response_code` (`response_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API request/response logs';

-- ------------------------------------------------------------
-- Sample Data
-- ------------------------------------------------------------
INSERT INTO `leads` (`lead_uuid`, `source`, `first_name`, `last_name`, `email`, `phone_primary`, `country`, `state`, `city`, `status`, `priority`, `score`, `opt_in_call`, `attempt_count`) VALUES
('LEAD-2026-0001', 'web_form', 'Alice',   'Williams', 'alice.w@example.com',   '+14155551001', 'US', 'California',   'San Francisco', 'new',       'high',   85, 1, 0),
('LEAD-2026-0002', 'api',      'Robert',  'Davis',    'robert.d@example.com',  '+14085551002', 'US', 'Texas',        'Austin',        'assigned',  'medium', 62, 1, 1),
('LEAD-2026-0003', 'import',   'Priya',   'Sharma',   'priya.s@example.com',   '+919876543210','IN', 'Maharashtra',  'Mumbai',        'contacted', 'low',    40, 1, 3),
('LEAD-2026-0004', 'referral', 'James',   'Wilson',   'james.w@example.com',   '+442071234567','GB', 'England',      'London',        'qualified', 'urgent', 91, 1, 2),
('LEAD-2026-0005', 'web_form', 'Sophie',  'Martin',   'sophie.m@example.com',  '+14255551005', 'US', 'Washington',   'Seattle',       'new',       'medium', 55, 1, 0);

INSERT INTO `customers` (`customer_ref`, `lead_id`, `account_type`, `first_name`, `last_name`, `email`, `phone_primary`, `country`, `city`, `segment`, `account_status`, `lifetime_value`, `total_calls`, `contract_start`) VALUES
('CUST-2026-0001', 4, 'individual', 'James',  'Wilson',  'james.w@example.com',   '+442071234567', 'GB', 'London',        'SMB',        'active', 12500.00, 18, '2026-01-15'),
('CUST-2026-0002', NULL, 'business', 'Acme',  'Corp',    'billing@acme.example',  '+18005551000',  'US', 'New York',      'Enterprise', 'active', 98000.00, 142,'2025-06-01'),
('CUST-2026-0003', NULL, 'individual','Maria','Garcia',  'maria.g@example.com',   '+13055551003',  'US', 'Miami',         'SMB',        'active', 3200.00,  7,  '2026-03-10');

INSERT INTO `api_integrations` (`name`, `provider`, `type`, `endpoint_url`, `api_key`, `auth_type`, `is_active`, `event_triggers`) VALUES
('Salesforce CRM Sync',  'salesforce', 'crm',       'https://api.salesforce.com/v55.0', 'sf_api_key_here', 'oauth2',   1, '["lead.created","lead.converted","call.ended"]'),
('HubSpot Marketing',    'hubspot',    'marketing',  'https://api.hubapi.com/v3',        'hs_api_key_here', 'api_key',  0, '["lead.created","interaction.logged"]'),
('Zendesk Helpdesk',     'zendesk',    'helpdesk',   'https://company.zendesk.com/api',  'zd_api_key_here', 'basic',    1, '["customer.complaint","call.voicemail"]');
