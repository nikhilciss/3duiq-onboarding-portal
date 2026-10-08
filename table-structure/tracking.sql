-- ============================================================
-- 3duiq Onboarding Portal - Tracking Database Schema
-- File: tracking.sql
-- Description: Call tracking, session logs, agent activity
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ------------------------------------------------------------
-- Table: calls
-- Stores individual call records
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `calls` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `call_uuid`         VARCHAR(100)    NOT NULL UNIQUE COMMENT 'Unique call identifier',
    `session_id`        VARCHAR(100)    DEFAULT NULL,
    `agent_id`          BIGINT UNSIGNED DEFAULT NULL,
    `campaign_id`       BIGINT UNSIGNED DEFAULT NULL,
    `caller_number`     VARCHAR(20)     NOT NULL,
    `dialed_number`     VARCHAR(20)     NOT NULL,
    `direction`         ENUM('inbound','outbound','internal') NOT NULL DEFAULT 'inbound',
    `status`            ENUM('initiated','ringing','answered','busy','no_answer','failed','voicemail') NOT NULL DEFAULT 'initiated',
    `start_time`        DATETIME        NOT NULL,
    `answer_time`       DATETIME        DEFAULT NULL,
    `end_time`          DATETIME        DEFAULT NULL,
    `duration_seconds`  INT UNSIGNED    DEFAULT 0 COMMENT 'Total call duration in seconds',
    `talk_time_seconds` INT UNSIGNED    DEFAULT 0 COMMENT 'Actual talk time in seconds',
    `hold_time_seconds` INT UNSIGNED    DEFAULT 0 COMMENT 'Total hold time in seconds',
    `recording_url`     VARCHAR(500)    DEFAULT NULL,
    `disposition`       VARCHAR(100)    DEFAULT NULL COMMENT 'Call outcome e.g. sale, callback, not_interested',
    `notes`             TEXT            DEFAULT NULL,
    `hangup_cause`      VARCHAR(100)    DEFAULT NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_agent_id` (`agent_id`),
    INDEX `idx_campaign_id` (`campaign_id`),
    INDEX `idx_start_time` (`start_time`),
    INDEX `idx_status` (`status`),
    INDEX `idx_direction` (`direction`),
    INDEX `idx_caller_number` (`caller_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual call records';

-- ------------------------------------------------------------
-- Table: agents
-- Call center agent profiles
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agents` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `agent_code`    VARCHAR(50)     NOT NULL UNIQUE,
    `first_name`    VARCHAR(100)    NOT NULL,
    `last_name`     VARCHAR(100)    NOT NULL,
    `email`         VARCHAR(191)    NOT NULL UNIQUE,
    `phone`         VARCHAR(20)     DEFAULT NULL,
    `extension`     VARCHAR(20)     DEFAULT NULL,
    `team_id`       BIGINT UNSIGNED DEFAULT NULL,
    `supervisor_id` BIGINT UNSIGNED DEFAULT NULL,
    `status`        ENUM('available','busy','on_break','offline') NOT NULL DEFAULT 'offline',
    `skill_level`   TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=Junior, 2=Mid, 3=Senior',
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `hired_at`      DATE            DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_team_id` (`team_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Call center agents';

-- ------------------------------------------------------------
-- Table: campaigns
-- Outbound dialing campaigns
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaigns` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(200)    NOT NULL,
    `description`     TEXT            DEFAULT NULL,
    `type`            ENUM('predictive','preview','progressive','inbound') NOT NULL DEFAULT 'preview',
    `status`          ENUM('draft','active','paused','completed','archived') NOT NULL DEFAULT 'draft',
    `start_date`      DATE            DEFAULT NULL,
    `end_date`        DATE            DEFAULT NULL,
    `daily_start_time` TIME           DEFAULT '09:00:00',
    `daily_end_time`  TIME            DEFAULT '18:00:00',
    `max_attempts`    TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `caller_id`       VARCHAR(20)     DEFAULT NULL,
    `script`          LONGTEXT        DEFAULT NULL,
    `total_leads`     INT UNSIGNED    DEFAULT 0,
    `contacted`       INT UNSIGNED    DEFAULT 0,
    `converted`       INT UNSIGNED    DEFAULT 0,
    `created_by`      BIGINT UNSIGNED DEFAULT NULL,
    `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`type`),
    INDEX `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dialing campaigns';

-- ------------------------------------------------------------
-- Table: agent_sessions
-- Agent login/logout session tracking
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agent_sessions` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `agent_id`       BIGINT UNSIGNED NOT NULL,
    `login_at`       DATETIME        NOT NULL,
    `logout_at`      DATETIME        DEFAULT NULL,
    `session_duration` INT UNSIGNED  DEFAULT NULL COMMENT 'Duration in seconds',
    `total_calls`    INT UNSIGNED    DEFAULT 0,
    `total_talk_time` INT UNSIGNED   DEFAULT 0,
    `total_hold_time` INT UNSIGNED   DEFAULT 0,
    `break_time`     INT UNSIGNED    DEFAULT 0,
    `ip_address`     VARCHAR(45)     DEFAULT NULL,
    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_agent_id` (`agent_id`),
    INDEX `idx_login_at` (`login_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Agent login sessions';

-- ------------------------------------------------------------
-- Table: call_events
-- Real-time events during a call (hold, transfer, conference)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `call_events` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `call_id`    BIGINT UNSIGNED NOT NULL,
    `event_type` ENUM('hold','unhold','transfer','conference','mute','unmute','dtmf','note') NOT NULL,
    `event_data` JSON            DEFAULT NULL,
    `occurred_at` DATETIME       NOT NULL,
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_call_id` (`call_id`),
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_occurred_at` (`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Events occurring during a call';

-- ------------------------------------------------------------
-- Sample Data
-- ------------------------------------------------------------
INSERT INTO `agents` (`agent_code`, `first_name`, `last_name`, `email`, `extension`, `status`, `skill_level`, `is_active`, `hired_at`) VALUES
('AGT-001', 'John',  'Smith',   'john.smith@company.local',   '1001', 'available', 2, 1, '2024-01-10'),
('AGT-002', 'Sarah', 'Johnson', 'sarah.johnson@company.local', '1002', 'offline',   3, 1, '2023-06-15'),
('AGT-003', 'Mike',  'Brown',   'mike.brown@company.local',    '1003', 'on_break',  1, 1, '2024-03-20');

INSERT INTO `campaigns` (`name`, `description`, `type`, `status`, `start_date`, `end_date`, `max_attempts`, `caller_id`, `total_leads`) VALUES
('Q4 Sales Drive',      'End of year sales campaign',    'predictive', 'active',    '2026-10-01', '2026-12-31', 3, '18005550100', 5000),
('Customer Winback',    'Re-engage churned customers',   'preview',    'paused',    '2026-09-01', '2026-11-30', 4, '18005550101', 1200),
('Inbound Support Q4',  'General inbound support queue', 'inbound',    'active',    '2026-10-01', NULL,         1, NULL,          0);

INSERT INTO `calls` (`call_uuid`, `agent_id`, `campaign_id`, `caller_number`, `dialed_number`, `direction`, `status`, `start_time`, `answer_time`, `end_time`, `duration_seconds`, `talk_time_seconds`, `disposition`) VALUES
('CALL-2026-0001', 1, 1, '+14155550100', '+18005550100', 'inbound',  'answered', '2026-09-24 09:00:00', '2026-09-24 09:00:15', '2026-09-24 09:08:22', 502, 487, 'sale'),
('CALL-2026-0002', 2, 1, '+12125550199', '+18005550100', 'outbound', 'answered', '2026-09-24 09:15:00', '2026-09-24 09:15:45', '2026-09-24 09:22:10', 430, 385, 'callback'),
('CALL-2026-0003', 1, 2, '+13105550250', '+18005550101', 'outbound', 'no_answer','2026-09-24 10:00:00', NULL,                   '2026-09-24 10:00:30', 30,  0,   'no_answer');
