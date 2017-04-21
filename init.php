<?php
SOMUSIC_CLASS_EventHandler::getInstance ()->init ();

OW::getRouter()->addRoute(new OW_Route('somusic.admin', 'admin/plugins/somusic', "SOMUSIC_CTRL_Admin", 'instruments'));