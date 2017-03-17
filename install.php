<?php
$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'somusic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_owner` int(11) NOT NULL,
  `data` mediumtext NOT NULL,
  `title` varchar(255),
  `description` varchar(255),
  `timestamp_c` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `timestamp_m` TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'somusic_post` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_melody` int(11) NOT NULL,
    `id_post` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'instrument_group` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR(32) NOT NULL ,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'music_instrument` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(32) NOT NULL,
	`id_group` INT NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		
CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'instrument_score` (
	`id_instrument` INT NOT NULL,
	`id` INT NOT NULL AUTO_INCREMENT,
	`clef` ENUM("treble","bass","tenor","alto","soprano","mezzo-soprano","baritone-c") NOT NULL,
	PRIMARY KEY (`id_instrument`, `id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		
CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'instrument_score_in_braces` (
	`id_instrument` INT NOT NULL,
	`id_score_1` INT NOT NULL,
	`id_score_2` INT NOT NULL,
	`id` INT NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id_instrument`, `id_score_1`, `id_score_2`, `id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;';

OW::getDbo ()->query ( $sql );

$sql = "INSERT INTO `ow_instrument_group` (`id`, `name`)
		VALUES (NULL, 'Keyboards'), (NULL, 'Strings'), (NULL, 'Woodwinds'),
		(NULL, 'Brass'), (NULL, 'Plucked-strings'), (NULL, 'Vocals');
		
		INSERT INTO `ow_music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Accordion', '1'), (NULL, 'Organ', '1'), (NULL, 'Piano', '1');
		
		INSERT INTO `ow_music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Contrabass', '2'), (NULL, 'Viola', '2'), 
		(NULL, 'Violin', '2'), (NULL, 'Violoncello', '2');
		
		INSERT INTO `ow_music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Saxopone', '3'), (NULL, 'Bassoon', '3'), 
		(NULL, 'Clarinet', '3'), (NULL, 'English Horn', '3'), 
		(NULL, 'Flute', '3'), (NULL, 'Pan Flute', '3'), 
		(NULL, 'Piccolo', '3'), (NULL, 'Tin Whistle', '3');
		
		INSERT INTO `ow_music_instrument` (`id`, `name`, `id_group`) 
		VALUES ('16', 'Trombone', '4'), (NULL, 'Trumpet', '4'), (NULL, 'Tuba', '4');
		
		INSERT INTO `ow_music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, 'Banjo', '5'), (NULL, 'Bass', '5'), (NULL, 'Guitar', '5'), 
		(NULL, 'Harp', '5'), (NULL, 'Sitar', '5'), (NULL, 'Ukulele', '5');
		
		INSERT INTO `ow_music_instrument` (`id`, `name`, `id_group`) 
		VALUES (NULL, '4 Voices', '6'), (NULL, 'Singer Voice', '6');
		
		INSERT INTO `ow_instrument_score` (`id_instrument`, `id`, `clef`) 
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
		
		INSERT INTO `ow_instrument_score_in_braces` (`id_instrument`, `id_score_1`, `id_score_2`, `id`) 
		VALUES ('2', '1', '2', NULL), ('3', '1', '2', NULL), ('22', '1', '2', NULL);";

OW::getDbo ()->query ( $sql );
