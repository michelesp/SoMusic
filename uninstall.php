<?php
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'somusic;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'somusic_post;';
OW::getDbo ()->query ( $sql );

$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'instrument_score_in_braces;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'instrument_score;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'music_instrument;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'instrument_group;';
OW::getDbo ()->query ( $sql );
/*
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'composition;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'composition_instrument_score;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'score_permits_modification;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'measure;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'note;';
OW::getDbo ()->query ( $sql );
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'tie;';
OW::getDbo ()->query ( $sql );
*/
$sql = 'DROP TABLE ' . OW_DB_PREFIX . 'assignment;';
OW::getDbo ()->query ( $sql );
