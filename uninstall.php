<?php

$prefix = OW_DB_PREFIX."somusic_";

$sql = 'DROP TABLE '.$prefix.'composition;
		DROP TABLE '.$prefix.'post;
		DROP TABLE '.$prefix.'music_instrument;
		DROP TABLE '.$prefix.'instrument_group;
		DROP TABLE '.$prefix.'assignment;
		DROP TABLE '.$prefix.'assignment_execution;
		DROP TABLE '.$prefix.'users_compositions_similarity;';

OW::getDbo()->query($sql);

