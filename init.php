<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * /init.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests
 * @since 1.3.1
 */

OW::getRouter()->addRoute(
    new OW_Route('ocsguests.admin', '/admin/plugins/ocsguests', 'OCSGUESTS_CTRL_Admin', 'index')
);

OW::getRouter()->addRoute(
    new OW_Route('ocsguests.list', '/guests/list', 'OCSGUESTS_CTRL_List', 'index')
);


function ocsguests_track_visit()
{
    $attrs = OW::getDispatcher()->getDispatchAttributes();

    if ( $attrs['controller'] == 'BASE_CTRL_ComponentPanel' && $attrs['action'] == 'profile' )
    {
        $username = $attrs['params']['username'];
        
        $user = BOL_UserService::getInstance()->findByUsername($username);
        $userId = $user->id;
        $viewerId = OW::getUser()->getId();
        
        if ( $viewerId && $viewerId != $userId )
        {
            OCSGUESTS_BOL_Service::getInstance()->trackVisit($userId, $viewerId);
        }
    }
}
OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, 'ocsguests_track_visit');

function ocsguests_on_user_unregister( OW_Event $event )
{
    $params = $event->getParams();

    $userId = $params['userId'];

    OCSGUESTS_BOL_Service::getInstance()->deleteUserGuests($userId);
}

OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, 'ocsguests_on_user_unregister');