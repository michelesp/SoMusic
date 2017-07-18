<?php
//OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'vm.index', 'vm', 'routing_vm_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('SOMUSIC_CMP_AssignmentsWidget', true);
$widgetPlace = $widgetService->addWidgetToPlace($widget, "group");
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);

$widget = $widgetService->addWidget('SOMUSIC_CMP_CompositionWidget', true);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'somusic.compositions-similarity', 'somusic', 'main_menu_similarity', OW_Navigation::VISIBLE_FOR_MEMBER);


$widget = $widgetService->addWidget('GROUPS_CMP_GroupsWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

$widget = $widgetService->addWidget('BASE_CMP_UserListWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT);

