<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Data Access Object for `ocsguests_guest` table.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests.bol
 * @since 1.3.1
 */
class OCSGUESTS_BOL_GuestDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var OCSGUESTS_BOL_GuestDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return OCSGUESTS_BOL_GuestDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     */
    public function getDtoClassName()
    {
        return 'OCSGUESTS_BOL_Guest';
    }
    
    /**
     * @see OW_BaseDao::getTableName()
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'ocsguests_guest';
    }
    
    public function findGuest( $userId, $guestId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('userId', $userId);
    	$example->andFieldEqual('guestId', $guestId);
    	
    	return $this->findObjectByExample($example);
    }
    
    public function findUserGuests( $userId, $page, $limit )
    {
    	$first = ( $page - 1 ) * $limit;
    	
    	$example = new OW_Example();
    	$example->andFieldEqual('userId', $userId);
    	$example->setLimitClause($first, $limit);
    	$example->setOrder('`visitTimestamp` DESC');
    	
    	return $this->findListByExample($example);
    }
    
    public function findGuestUsers( $userId, $page, $limit )
    {
    	$first = ( $page - 1 ) * $limit;
    	
    	$query = "SELECT `u`.*
            FROM `".$this->getTableName()."` AS `g`
            INNER JOIN `" . BOL_UserDao::getInstance()->getTableName() . "` as `u` 
                ON (`g`.`guestId` = `u`.`id`)
            LEFT JOIN `" . BOL_UserSuspendDao::getInstance()->getTableName() . "` as `s`
                ON( `u`.`id` = `s`.`userId` )
            LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` as `d`
                ON( `u`.`id` = `d`.`userId` )
            WHERE `s`.`id` IS NULL AND `d`.`id` IS NULL
            AND `g`.`userId` = ?
            ORDER BY `g`.`visitTimestamp` DESC
            LIMIT ?, ?";

        return $this->dbo->queryForObjectList($query, BOL_UserDao::getInstance()->getDtoClassName(), array($userId, $first, $limit));
    }
    
    public function countUserGuests( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->countByExample($example);
    }
    
    public function deleteExpired( $timestamp )
    {
    	$example = new OW_Example();
    	$example->andFieldLessThan('visitTimestamp', time() - $timestamp);
    	
    	$this->deleteByExample($example);
    }
    
    public function deleteUserGuests( $userId )
    {
    	$sql = "DELETE FROM `".$this->getTableName()."` 
    	   WHERE `userId` = ? OR `guestId` = ?";
    	
    	$this->dbo->query($sql, array($userId, $userId));
    }
}
