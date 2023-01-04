/*
 * @package    SW JProjects Payment
 * @subpackage plugin system/swjprojects
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

CREATE TABLE IF NOT EXISTS `#__swjprojects_order`
(
    `id`                    int(11)      NOT NULL AUTO_INCREMENT,
    `key_id`                int(11)      NOT NULL,
    `payment_create_date`   datetime     NULL,
    `processor`             varchar(100) NOT NULL,
    `transaction_id`        varchar(100) NULL,
    `extra`                 text         NULL,
    `payment_received_date` datetime     NULL,
    `payment_status`        varchar(50)  NULL,
    PRIMARY KEY `id` (`id`),
    KEY `idx_key_id` (`key_id`(100))
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
    AUTO_INCREMENT = 0;