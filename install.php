<?php

$prefix = OW_DB_PREFIX."somusic_";

$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'composition` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`user_c` INT NOT NULL,
	`timestamp_c` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`name` VARCHAR(256) NOT NULL,
	`user_m` INT NOT NULL,
	`timestamp_m` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`instrumentsScore` VARCHAR(32767) NOT NULL,
	`instrumentsUsed` VARCHAR(4096) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `'.$prefix.'post` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_melody` int(11) NOT NULL,
    `id_post` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `'.$prefix.'instrument_group` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR(32) NOT NULL ,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `'.$prefix.'music_instrument` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(32) NOT NULL,
	`id_group` INT NOT NULL,
	`scoresClef` VARCHAR(255) NOT NULL,
	`braces` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `'.$prefix.'assignment` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(32) NOT NULL, 
	`group_id` INT NOT NULL,
	`timestamp_c` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`timestamp_m` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`last_user_m` INT NOT NULL,
	`mode` INT NOT NULL,
	`composition_id` INT NOT NULL,
	`close` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE = MyISAM;
		
CREATE TABLE IF NOT EXISTS `'.$prefix.'assignment_execution` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`composition_id` INT NOT NULL,
	`assignment_id` INT NOT NULL,
	`user_id` INT NOT NULL,
	`comment` VARCHAR(255) NOT NULL DEFAULT "",
	PRIMARY KEY (`id`)
) ENGINE = MyISAM;
		
CREATE TABLE IF NOT EXISTS `'.$prefix.'users_compositions_similarity` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`userId1` INT NOT NULL,
	`userId2` INT NOT NULL,
	`value` FLOAT NOT NULL,
	`last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE = MyISAM;';

OW::getDbo()->query($sql);

$pianoBraces = json_encode(array(array(0, 1)));
$commonBraces = json_encode(array());
$pianoScoresClef = json_encode(array("treble", "bass"));
$organScoresClef = json_encode(array("treble", "bass", "bass"));
$trebleScoresClef = json_encode(array("treble"));
$bassScoresClef = json_encode(array("bass"));
$altoScoresClef = json_encode(array("alto"));

$sql = 'INSERT INTO `'.$prefix.'instrument_group` (`id`, `name`)
		VALUES (NULL, "Keyboards"), (NULL, "Strings"), (NULL, "Woodwinds"),
		(NULL, "Brass"), (NULL, "Plucked-strings"), (NULL, "Vocals");';
OW::getDbo()->query($sql);

$sql = 'INSERT INTO `'.$prefix.'music_instrument` (`id`, `name`, `id_group`, `scoresClef`, `braces`) 
		VALUES (NULL, "Accordion", 1, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Organ", 1, \''.$organScoresClef.'\', \''.$pianoBraces.'\'),
				(NULL, "Piano", 1, \''.$pianoScoresClef.'\', \''.$pianoBraces.'\');';
OW::getDbo()->query($sql);

$sql = 'INSERT INTO `'.$prefix.'music_instrument` (`id`, `name`, `id_group`, `scoresClef`, `braces`) 
		VALUES (NULL, "Contrabass", 2, \''.$bassScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Viola", 2, \''.$altoScoresClef.'\', \''.$commonBraces.'\'), 
				(NULL, "Violin", 2, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Violoncello", 2, \''.$bassScoresClef.'\', \''.$commonBraces.'\');';
OW::getDbo()->query($sql);

$sql = 'INSERT INTO `'.$prefix.'music_instrument` (`id`, `name`, `id_group`, `scoresClef`, `braces`) 
		VALUES (NULL, "Saxopone", 3, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Bassoon", 3, \''.$bassScoresClef.'\', \''.$commonBraces.'\'), 
				(NULL, "Clarinet", 3, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "English Horn", 3, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'), 
				(NULL, "Flute", 3, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Pan Flute", 3, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'), 
				(NULL, "Piccolo", 3, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Tin Whistle", 3, \''.$trebleScoresClef.'\', \''.$commonBraces.'\');';
OW::getDbo()->query($sql);

$sql = 'INSERT INTO `'.$prefix.'music_instrument` (`id`, `name`, `id_group`, `scoresClef`, `braces`) 
		VALUES (NULL, "Trombone", 4, \''.$bassScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Trumpet", 4, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Tuba", 4, \''.$bassScoresClef.'\', \''.$commonBraces.'\');';
OW::getDbo()->query($sql);

$sql = 'INSERT INTO `'.$prefix.'music_instrument` (`id`, `name`, `id_group`, `scoresClef`, `braces`) 
		VALUES (NULL, "Banjo", 5, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Bass", 5, \''.$bassScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Guitar", 5, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'), 
				(NULL, "Harp", 5, \''.$pianoScoresClef.'\', \''.$pianoBraces.'\'),
				(NULL, "Sitar", 5, \''.$trebleScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Ukulele", 5, \''.$bassScoresClef.'\', \''.$commonBraces.'\');';
OW::getDbo()->query($sql);

$sql = 'INSERT INTO `'.$prefix.'music_instrument` (`id`, `name`, `id_group`, `scoresClef`, `braces`) 
		VALUES (NULL, "4 Voices", 6, \''.$pianoScoresClef.'\', \''.$commonBraces.'\'),
				(NULL, "Singer Voice", 6, \''.$trebleScoresClef.'\', \''.$commonBraces.'\');';
OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('somusic', 'somusic.admin');

$path = OW::getPluginManager()->getPlugin('somusic')->getRootDir().'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'somusic');

