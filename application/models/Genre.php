<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:03
 */

class Application_Model_Genre extends Zend_Db_Table_Row_Abstract{
    protected $_table = 'Genre';
    protected $_tableClass = 'Application_Model_Genre_Table';
}