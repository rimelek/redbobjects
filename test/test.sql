-- table
CREATE TABLE IF NOT EXISTS `prefix_ranks`
(
    `rankid`  INT UNSIGNED                                           NOT NULL AUTO_INCREMENT,
    `name`    VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'the name of the rank to display',
    `varname` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'the name of the variable',
    PRIMARY KEY (`rankid`),
    UNIQUE INDEX `unique` (`varname` ASC)
)
    ENGINE = MyISAM
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_general_ci;

-- table
INSERT INTO `prefix_ranks` (`rankid`, `name`, `varname`)
VALUES (5, 'Guest', 'guest'),
       (1, 'Owner', 'owner'),
       (2, 'Administrator', 'admin'),
       (3, 'User', 'user'),
       (4, 'Banned', 'banned');

-- table
UPDATE `prefix_ranks`
SET `rankid` = '0'
WHERE `rankid` = 5;
