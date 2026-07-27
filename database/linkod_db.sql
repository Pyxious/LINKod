-- ============================================================
--  LINKod Database Schema
--  Database: linkod_db
--  Created : 2026-07-10
-- ============================================================

CREATE DATABASE IF NOT EXISTS linkod_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE linkod_db;

-- ============================================================
--  TABLE: USER
-- ============================================================
CREATE TABLE IF NOT EXISTS `USER` (
    `user_id`        INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `username`       VARCHAR(50)      NOT NULL UNIQUE,
    `first_name`     VARCHAR(50)      NOT NULL,
    `last_name`      VARCHAR(50)      NOT NULL,
    `middle_name`    VARCHAR(50)          NULL,
    `date_of_birth`  DATE                 NULL,
    `email_account`  VARCHAR(100)     NOT NULL UNIQUE,
    `contact_number` VARCHAR(20)          NULL,
    `role`           ENUM(
                         'admin',
                         'staff',
                         'client',
                         'worker'
                     )                NOT NULL DEFAULT 'client',
    `password`       VARCHAR(255)     NOT NULL,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: USER_LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS `USER_LOG` (
    `log_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NOT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME         NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    CONSTRAINT `fk_userlog_user`
        FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: NOTIFICATION
-- ============================================================
CREATE TABLE IF NOT EXISTS `NOTIFICATION` (
    `notification_id` INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED  NOT NULL,
    `sent_at`         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `type`            VARCHAR(50)       NULL,
    `title`           VARCHAR(150)      NULL,
    `message`         TEXT              NULL,
    `is_read`         TINYINT(1)    NOT NULL DEFAULT 0,
    PRIMARY KEY (`notification_id`),
    CONSTRAINT `fk_notif_user`
        FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: CLIENT
-- ============================================================
CREATE TABLE IF NOT EXISTS `CLIENT` (
    `client_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`   INT UNSIGNED NOT NULL,
    `office`    VARCHAR(100)     NULL,
    `campus`    VARCHAR(100)     NULL,
    PRIMARY KEY (`client_id`),
    CONSTRAINT `fk_client_user`
        FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: STAFF
-- ============================================================
CREATE TABLE IF NOT EXISTS `STAFF` (
    `staff_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `role`       VARCHAR(50)      NULL,
    `date_hired` DATE             NULL,
    PRIMARY KEY (`staff_id`),
    CONSTRAINT `fk_staff_user`
        FOREIGN KEY (`user_id`) REFERENCES `USER` (`user_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: TEAM
-- ============================================================
CREATE TABLE IF NOT EXISTS `TEAM` (
    `team_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `team_name`    VARCHAR(100) NOT NULL,
    `team_leader`  INT UNSIGNED     NULL,
    `member_count` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`team_id`)
    -- team_leader FK added after TEAM_LEADER table is created
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: TEAM_LEADER
-- ============================================================
CREATE TABLE IF NOT EXISTS `TEAM_LEADER` (
    `leader_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `staff_id`  INT UNSIGNED NOT NULL,
    PRIMARY KEY (`leader_id`),
    CONSTRAINT `fk_teamleader_staff`
        FOREIGN KEY (`staff_id`) REFERENCES `STAFF` (`staff_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add FK from TEAM.team_leader -> TEAM_LEADER.leader_id now that the table exists
ALTER TABLE `TEAM`
    ADD CONSTRAINT `fk_team_leader`
        FOREIGN KEY (`team_leader`) REFERENCES `TEAM_LEADER` (`leader_id`)
        ON UPDATE CASCADE ON DELETE SET NULL;

-- ============================================================
--  TABLE: WORKER
-- ============================================================
CREATE TABLE IF NOT EXISTS `WORKER` (
    `worker_id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `staff_id`     INT UNSIGNED NOT NULL,
    `team_id`      INT UNSIGNED     NULL,
    `date_hired`   DATE             NULL,
    `is_available` TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (`worker_id`),
    CONSTRAINT `fk_worker_staff`
        FOREIGN KEY (`staff_id`) REFERENCES `STAFF` (`staff_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_worker_team`
        FOREIGN KEY (`team_id`) REFERENCES `TEAM` (`team_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: CATEGORY
-- ============================================================
CREATE TABLE IF NOT EXISTS `CATEGORY` (
    `category_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_name` VARCHAR(100) NOT NULL,
    `description`   TEXT             NULL,
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: REQUEST
-- ============================================================
CREATE TABLE IF NOT EXISTS `REQUEST` (
    `request_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`    INT UNSIGNED NOT NULL,
    `category_id`  INT UNSIGNED     NULL,
    `title`        VARCHAR(150) NOT NULL,
    `description`  TEXT             NULL,
    `location`     VARCHAR(255)     NULL,
    `complexity`   ENUM('low','medium','high')   NULL,
    `urgency`      ENUM('low','medium','high')   NULL,
    `priority`     ENUM('low','medium','high')   NULL,
    `attachment`   VARCHAR(500)     NULL,
    `submitted_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`request_id`),
    CONSTRAINT `fk_request_client`
        FOREIGN KEY (`client_id`) REFERENCES `CLIENT` (`client_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_request_category`
        FOREIGN KEY (`category_id`) REFERENCES `CATEGORY` (`category_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: REQUEST_HISTORY
-- ============================================================
CREATE TABLE IF NOT EXISTS `REQUEST_HISTORY` (
    `history_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_id`      INT UNSIGNED NOT NULL,
    `previous_status` VARCHAR(50)      NULL,
    `current_status`  VARCHAR(50)      NULL,
    `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_by`      INT UNSIGNED     NULL,
    PRIMARY KEY (`history_id`),
    CONSTRAINT `fk_reqhist_request`
        FOREIGN KEY (`request_id`) REFERENCES `REQUEST` (`request_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_reqhist_updatedby`
        FOREIGN KEY (`updated_by`) REFERENCES `USER` (`user_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: EVALUATION
-- ============================================================
CREATE TABLE IF NOT EXISTS `EVALUATION` (
    `evaluation_id` INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `client_id`     INT UNSIGNED   NOT NULL,
    `request_id`    INT UNSIGNED   NOT NULL,
    `rating`        TINYINT UNSIGNED   NULL CHECK (`rating` BETWEEN 1 AND 5),
    `feedback_text` TEXT               NULL,
    `rated_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`evaluation_id`),
    CONSTRAINT `fk_eval_client`
        FOREIGN KEY (`client_id`) REFERENCES `CLIENT` (`client_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_eval_request`
        FOREIGN KEY (`request_id`) REFERENCES `REQUEST` (`request_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: PROJECT
-- ============================================================
CREATE TABLE IF NOT EXISTS `PROJECT` (
    `project_id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`      INT UNSIGNED NOT NULL,
    `request_id`     INT UNSIGNED     NULL,
    `approved_by`    INT UNSIGNED     NULL,
    `date_approved`  DATE             NULL,
    `recommendation` TEXT             NULL,
    PRIMARY KEY (`project_id`),
    CONSTRAINT `fk_project_client`
        FOREIGN KEY (`client_id`) REFERENCES `CLIENT` (`client_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_project_request`
        FOREIGN KEY (`request_id`) REFERENCES `REQUEST` (`request_id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_project_approvedby`
        FOREIGN KEY (`approved_by`) REFERENCES `STAFF` (`staff_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: PROJECT_HISTORY
-- ============================================================
CREATE TABLE IF NOT EXISTS `PROJECT_HISTORY` (
    `phistory_id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id`      INT UNSIGNED NOT NULL,
    `previous_status` VARCHAR(50)      NULL,
    `current_status`  VARCHAR(50)      NULL,
    `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_by`      INT UNSIGNED     NULL,
    PRIMARY KEY (`phistory_id`),
    CONSTRAINT `fk_projhist_project`
        FOREIGN KEY (`project_id`) REFERENCES `PROJECT` (`project_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_projhist_updatedby`
        FOREIGN KEY (`updated_by`) REFERENCES `USER` (`user_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: PROJECT_WORKER  (assignment bridge)
-- ============================================================
CREATE TABLE IF NOT EXISTS `PROJECT_WORKER` (
    `assignment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `worker_id`     INT UNSIGNED NOT NULL,
    `project_id`    INT UNSIGNED NOT NULL,
    `date_assigned` DATE             NULL,
    PRIMARY KEY (`assignment_id`),
    CONSTRAINT `fk_pw_worker`
        FOREIGN KEY (`worker_id`) REFERENCES `WORKER` (`worker_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_pw_project`
        FOREIGN KEY (`project_id`) REFERENCES `PROJECT` (`project_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: MATERIALS
-- ============================================================
CREATE TABLE IF NOT EXISTS `MATERIALS` (
    `material_id`       INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `material_name`     VARCHAR(150)     NOT NULL,
    `unit_of_measurement` VARCHAR(50)        NULL,
    `unit_cost`         DECIMAL(12, 2)   NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  TABLE: BILL_OF_MATERIALS
-- ============================================================
CREATE TABLE IF NOT EXISTS `BILL_OF_MATERIALS` (
    `bom_id`       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `project_id`   INT UNSIGNED    NOT NULL,
    `material_id`  INT UNSIGNED    NOT NULL,
    `qty`          DECIMAL(10, 2)  NOT NULL DEFAULT 0,
    `total_cost`   DECIMAL(14, 2)  NOT NULL DEFAULT 0.00,
    `created_by`   INT UNSIGNED        NULL,
    `fulfilled_by` INT UNSIGNED        NULL,
    `date_approved` DATE               NULL,
    PRIMARY KEY (`bom_id`),
    CONSTRAINT `fk_bom_project`
        FOREIGN KEY (`project_id`) REFERENCES `PROJECT` (`project_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_bom_material`
        FOREIGN KEY (`material_id`) REFERENCES `MATERIALS` (`material_id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_bom_createdby`
        FOREIGN KEY (`created_by`) REFERENCES `STAFF` (`staff_id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_bom_fulfilledby`
        FOREIGN KEY (`fulfilled_by`) REFERENCES `STAFF` (`staff_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
