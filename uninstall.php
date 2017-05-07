<?php

$prefix = OW_DB_PREFIX."somusic_";

$sql = 'DROP TABLE '.$prefix.'somusic_composition;
		DROP TABLE '.$prefix.'somusic_post;
		DROP TABLE '.$prefix.'instrument_score_in_braces;
		DROP TABLE '.$prefix.'instrument_score;
		DROP TABLE '.$prefix.'music_instrument;
		DROP TABLE '.$prefix.'instrument_group;
		DROP TABLE '.$prefix.'assignment;
		DROP TABLE '.$prefix.'assignment_execution;
		DROP TABLE '.$prefix.'somusic_users_compositions_similarity;';

OW::getDbo()->query($sql);

