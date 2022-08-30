<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of accounts
 *
 * @author Maxime
 */
require_once("../classes/mysql/Database.class.php");

class account {
    var $userid = null;
    var $username = null;
    var $lastlogin = null;
    var $ip = null;
    var $usergroups = null;
    
    function __construct($userid, $fetchData) {
        $this->userid = $userid;
        if (isset($fetchData) && (isset($userid))) {
            $db = new Database("MTA");
            $db->connect();
            $result = $db->query_first("SELECT * FROM accounts WHERE id='".$this->userid."' ");
            $this->username = $result['username'];
            $this->db->close();
            $this->db = null;
        }
    }

       function getUserid() {
       return $this->userid;
   }

   function getUsergroups() {
       return $this->usergroups;
   }

   function setUserid($userid) {
       $this->userid = $userid;
   }

   function setUsergroups($usergroups) {
       $this->usergroups = $usergroups;
   }

   function setDb($db) {
       $this->db = $db;
   }
   
   
       /* 
    function account($userid) {
        if (isset($userid)) {
            
        }
    }
    */
}
