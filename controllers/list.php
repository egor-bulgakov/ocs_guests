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

class OCSGUESTS_CMP_Users extends BASE_CMP_Users
{
	private $guests;
	
    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $guests )
    {
    	$this->guests = $guests;

    	parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
    }

    public function getFields( $userIdList )
    {
    	$lang = OW::getLanguage();
    	
        $fields = array();
        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {
            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }
         
            $fields[$uid][] = array('label' => $lang->text('ocsguests', 'visited').' ', 'value' => '<span class="ow_remark">'. $this->guests[$uid]->visitTimestamp.'</span>');
        }

        return $fields;
    }
}