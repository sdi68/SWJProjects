CREATE TABLE IF NOT EXISTS `#__swjprojects_pdf`
(
    `id`                    int(11)      NOT NULL AUTO_INCREMENT,
    `doc_id`                int(11)      NOT NULL,
    `pdf_create_date`       datetime     NULL,
    `pdf`                   text         NOT NULL,
    PRIMARY KEY `id` (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
    AUTO_INCREMENT = 0;