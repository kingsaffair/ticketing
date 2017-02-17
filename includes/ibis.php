<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 * Modified and updated by Conor Burgess 2013-2014
 *
 * Ibis class file for interfacing with the lookup web API
 *
 */
    
require_once(dirname(__FILE__) . '/ibis-client/ibisclient/client/IbisClientConnection.php');
require_once(dirname(__FILE__) . '/ibis-client/ibisclient/methods/PersonMethods.php');
    
class Ibis {
    
    /**
     * ibisPM
     * Get a PersonMethods obejct for Ibis
     *
     * @access protected
     *
     * @returns PersonMethods object for Ibis API
     */
    protected static function ibisPM()
    {
        static $ibisConn = null;
        static $ibisPM = null;
        
        if(is_null($ibisConn)) {
            $ibisConn = \IbisClientConnection::createConnection();
        }
        
        if(is_null($ibisPM)) {
            $ibisPM = new \PersonMethods($ibisConn);
        }
        
        return $ibisPM;
    }
    
    /**
     * getPerson
     * Fetches a person and their attributes from Ibis
     *
     * @param string $crsid User's CRSID.
     *
     * @access public
     *
     * @return IbisPerson
     */
    public static function getPerson($crsid)
    {
        $pm = Ibis::ibisPM();
        return $pm->getPerson("crsid", $crsid, "jdCollege,all_insts");
    }
}
?>