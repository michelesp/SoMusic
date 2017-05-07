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
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		
CREATE TABLE IF NOT EXISTS `'.$prefix.'instrument_score` (
	`id_instrument` INT NOT NULL,
	`id` INT NOT NULL AUTO_INCREMENT,
	`clef` ENUM("treble","bass","tenor","alto","soprano","mezzo-soprano","baritone-c") NOT NULL,
	PRIMARY KEY (`id_instrument`, `id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		
CREATE TABLE IF NOT EXISTS `'.$prefix.'instrument_score_in_braces` (
	`id_instrument` INT NOT NULL,
	`id_score_1` INT NOT NULL,
	`id_score_2` INT NOT NULL,
	`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id_instrument`, `id_score_1`, `id_score_2`, `id`)
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

$sql = "INSERT INTO `".$prefix."instrument_group` (`id`, `name`)
		VALUES (NULL, 'Keyboards'), (NULL, 'Strings'), (NULL, 'Woodwinds'),
		(NULL, 'Brass'), (NULL, 'Plucked-strings'), (NULL, 'Vocals');
		
		INSERT INTO `".$prefix."music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Accordion', '1'), (NULL, 'Organ', '1'), (NULL, 'Piano', '1');
		
		INSERT INTO `".$prefix."music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Contrabass', '2'), (NULL, 'Viola', '2'), 
		(NULL, 'Violin', '2'), (NULL, 'Violoncello', '2');
		
		INSERT INTO `".$prefix."music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Saxopone', '3'), (NULL, 'Bassoon', '3'), 
		(NULL, 'Clarinet', '3'), (NULL, 'English Horn', '3'), 
		(NULL, 'Flute', '3'), (NULL, 'Pan Flute', '3'), 
		(NULL, 'Piccolo', '3'), (NULL, 'Tin Whistle', '3');
		
		INSERT INTO `".$prefix."music_instrument` (`id`, `name`, `id_group`) 
		VALUES ('16', 'Trombone', '4'), (NULL, 'Trumpet', '4'), (NULL, 'Tuba', '4');
		
		INSERT INTO `".$prefix."music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Banjo', '5'), (NULL, 'Bass', '5'), (NULL, 'Guitar', '5'), 
		(NULL, 'Harp', '5'), (NULL, 'Sitar', '5'), (NULL, 'Ukulele', '5');
		
		INSERT INTO `".$prefix."music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, '4 Voices', '6'), (NULL, 'Singer Voice', '6');
		
		INSERT INTO `".$prefix."instrument_score` (`id_instrument`, `id`, `clef`) 
		VALUES ('1', NULL, 'treble'), ('2', NULL, 'treble'), ('2', NULL, 'bass'), 
		('2', NULL, 'bass'), ('3', NULL, 'treble'), ('3', NULL, 'bass'), 
		('4', NULL, 'bass'), ('5', NULL, 'alto'), ('6', NULL, 'treble'), 
		('7', NULL, 'bass'), ('8', NULL, 'treble'), ('9', NULL, 'bass'), 
		('10', NULL, 'treble'), ('11', NULL, 'treble'), ('12', NULL, 'treble'), 
		('13', NULL, 'treble'), ('14', NULL, 'treble'), ('15', NULL, 'treble'), 
		('16', NULL, 'bass'), ('17', NULL, 'treble'), ('18', NULL, 'bass'), 
		('19', NULL, 'treble'), ('20', NULL, 'bass'), ('21', NULL, 'treble'), 
		('22', NULL, 'treble'), ('22', NULL, 'bass'), ('23', NULL, 'treble'), 
		('24', NULL, 'bass'), ('25', NULL, 'treble'), ('25', NULL, 'bass');
		
		INSERT INTO `".$prefix."instrument_score_in_braces` (`id_instrument`, `id_score_1`, `id_score_2`, `id`) 
		VALUES ('2', '1', '2', NULL), ('3', '1', '2', NULL), ('22', '1', '2', NULL);";

OW::getDbo ()->query ( $sql );

OW::getPluginManager()->addPluginSettingsRouteName('somusic', 'somusic.admin');

$path = OW::getPluginManager()->getPlugin('somusic')->getRootDir().'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'somusic');

