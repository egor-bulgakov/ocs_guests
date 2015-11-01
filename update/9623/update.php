<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

try
{
    Updater::getNavigationService()->addMenuItem(OW_Navigation::MOBILE_TOP, 'ocsguests_list', 'ocsguests', 'ocsguests_menu_item', OW_Navigation::VISIBLE_FOR_MEMBER);
}
catch(Exception $ex)
{
    
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'ocsguests');