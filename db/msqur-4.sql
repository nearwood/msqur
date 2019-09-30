ALTER TABLE `metadata` 
CHANGE COLUMN `msq` `msq` INT(11) NULL ,
CHANGE COLUMN `url` `url` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `views` `views` INT(10) UNSIGNED NULL DEFAULT 0 ,
CHANGE COLUMN `fileFormat` `fileFormat` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `signature` `signature` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL ,
CHANGE COLUMN `firmware` `firmware` VARCHAR(255) NULL ,
CHANGE COLUMN `author` `author` VARCHAR(255) NULL ,
CHANGE COLUMN `writeDate` `writeDate` DATETIME NULL ,
CHANGE COLUMN `uploadDate` `uploadDate` DATETIME NULL ,
CHANGE COLUMN `tuneComment` `tuneComment` TEXT NULL ,
CHANGE COLUMN `reingest` `reingest` TINYINT(1) NULL DEFAULT 0 ;