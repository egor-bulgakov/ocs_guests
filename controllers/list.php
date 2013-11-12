<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * User guests page controller.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests.controllers
 * @since 1.3.1
 */
class OCSGUESTS_CTRL_List extends OW_ActionController
{
    public function index( array $params )
    {
        if ( !$userId = OW::getUser()->getId() )
        {
            throw new AuthenticationException();
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        
        $perPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
        $guests = OCSGUESTS_BOL_Service::getInstance()->findGuestsForUser($userId, $page, $perPage);
        $guestsUsers = OCSGUESTS_BOL_Service::getInstance()->findGuestUsers($userId, $page, $perPage);
        
        $guestList = array();
        if ( $guests )
        {
        	foreach ( $guests as $guest )
        	{
        		$guestList[$guest->guestId] = $guest;
        	}
	        $itemCount = OCSGUESTS_BOL_Service::getInstance()->countGuestsForUser($userId);

	        $cmp = new OCSGUESTS_CMP_Users($guestsUsers, $itemCount, $perPage, true, $guestList);
	        $this->addComponent('guests', $cmp);
        }
        else 
        {
        	$this->assign('guests', null);
        }
        
        $this->setPageHeading(OW::getLanguage()->text('ocsguests', 'viewed_profile'));
        $this->setPageHeadingIconClass('ow_ic_user');
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'dashboard');
    }
}