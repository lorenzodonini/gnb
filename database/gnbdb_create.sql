-- MySQL Script generated by MySQL Workbench
-- Mon 02 Nov 2015 08:43:52 PM CET
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema gnbdb
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `gnbdb` ;

-- -----------------------------------------------------
-- Schema gnbdb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `gnbdb` DEFAULT CHARACTER SET utf8 ;
SHOW WARNINGS;
USE `gnbdb` ;

-- -----------------------------------------------------
-- Table `gnbdb`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gnbdb`.`user` ;

SHOW WARNINGS;
CREATE TABLE IF NOT EXISTS `gnbdb`.`user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(45) NOT NULL,
  `last_name` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `approved_by_user_id` INT NULL,
  `status` INT NOT NULL DEFAULT 0,
  `role` INT NOT NULL DEFAULT 0,
  `pw_salt` VARCHAR(45) NOT NULL,
  `pw_hash` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  INDEX `fk_approved_by_idx` (`approved_by_user_id` ASC),
  CONSTRAINT `fk_approved_by`
    FOREIGN KEY (`approved_by_user_id`)
    REFERENCES `gnbdb`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `gnbdb`.`account`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gnbdb`.`account` ;

SHOW WARNINGS;
CREATE TABLE IF NOT EXISTS `gnbdb`.`account` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_client_idx` (`user_id` ASC),
  CONSTRAINT `fk_client`
    FOREIGN KEY (`user_id`)
    REFERENCES `gnbdb`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 10000001;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `gnbdb`.`tan`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gnbdb`.`tan` ;

SHOW WARNINGS;
CREATE TABLE IF NOT EXISTS `gnbdb`.`tan` (
  `id` VARCHAR(15) NOT NULL,
  `account_id` INT NOT NULL,
  `used_timestamp` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_bank_account_id_idx` (`account_id` ASC),
  CONSTRAINT `fk_bank_account_id`
    FOREIGN KEY (`account_id`)
    REFERENCES `gnbdb`.`account` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `gnbdb`.`transaction`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gnbdb`.`transaction` ;

SHOW WARNINGS;
CREATE TABLE IF NOT EXISTS `gnbdb`.`transaction` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `approved_by_user_id` INT NULL,
  `approved_at` DATETIME NULL,
  `status` INT NOT NULL DEFAULT 0,
  `source_account_id` INT NOT NULL,
  `destination_account_id` INT NOT NULL,
  `creation_timestamp` DATETIME NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `description` VARCHAR(140) NOT NULL,
  `tan_id` VARCHAR(15) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_destination_account_idx` (`destination_account_id` ASC),
  INDEX `fk_source_account_idx` (`source_account_id` ASC),
  INDEX `fk_approved_by_user_idx` (`approved_by_user_id` ASC),
  UNIQUE INDEX `tan_id_UNIQUE` (`tan_id` ASC),
  CONSTRAINT `fk_destination_account`
    FOREIGN KEY (`destination_account_id`)
    REFERENCES `gnbdb`.`account` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_source_account`
    FOREIGN KEY (`source_account_id`)
    REFERENCES `gnbdb`.`account` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tan`
    FOREIGN KEY (`tan_id`)
    REFERENCES `gnbdb`.`tan` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_approved_by_user`
    FOREIGN KEY (`approved_by_user_id`)
    REFERENCES `gnbdb`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;
USE `gnbdb` ;

-- -----------------------------------------------------
-- Placeholder table for view `gnbdb`.`account_overview`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `gnbdb`.`account_overview` (`id` INT, `user_id` INT, `balance` INT);
SHOW WARNINGS;

-- -----------------------------------------------------
-- View `gnbdb`.`account_overview`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `gnbdb`.`account_overview` ;
SHOW WARNINGS;
DROP TABLE IF EXISTS `gnbdb`.`account_overview`;
SHOW WARNINGS;
USE `gnbdb`;
CREATE OR REPLACE VIEW `account_overview` AS

SELECT
	A.id,
    A.user_id,
    IF (A.user_id = 1, 10000000, 
    SUM(
		IF (A.id = T.destination_account_id AND T.status IN (0,1), T.amount, -T.amount))
        ) AS balance
FROM
	account A LEFT JOIN transaction T
    ON T.destination_account_id = A.id OR T.source_account_id = A.id
WHERE T.status != 2
GROUP BY A.id;
SHOW WARNINGS;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
