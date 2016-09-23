<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function run(){
        require_once('controllers/AbstractController.php');
        parent::run();
    }
    protected function _initMysql() {
        $this->bootstrap('db');
        $db = $this->getPluginResource('db')->getDbAdapter();
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        Zend_Registry::set('db', $db);
    }
}

