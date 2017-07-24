<?php
SOMUSIC_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('somusic.admin', 'admin/plugins/somusic', "SOMUSIC_CTRL_Admin", 'instruments'));

OW::getRouter()->addRoute(new OW_Route('somusic.my-space', 'myspace', "SOMUSIC_CTRL_MySpace", 'index'));
OW::getRouter()->addRoute(new OW_Route('somusic.compositions-similarity', 'users-compositions-similarity', "SOMUSIC_CTRL_UsersCompositionsSimilarity", 'index'));
