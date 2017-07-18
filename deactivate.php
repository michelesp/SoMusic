<?php
//OW::getNavigation()->deleteMenuItem('vm', 'routing_vm_menu_item');

BOL_ComponentAdminService::getInstance()->deleteWidget('SOMUSIC_CMP_AssignmentsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('SOMUSIC_CMP_CompositionWidget');

OW::getNavigation()->deleteMenuItem('somusic', 'main_menu_similarity');


BOL_ComponentAdminService::getInstance()->deleteWidget('GROUPS_CMP_GroupsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('BASE_CMP_UserListWidget');
