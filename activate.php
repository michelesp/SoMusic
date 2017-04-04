<?php
//OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'vm.index', 'vm', 'routing_vm_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('SOMUSIC_CMP_AssignmentsWidget', true);
$widgetPlace = $widgetService->addWidgetToPlace($widget, "group");
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);