<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:03
 */

class Application_Model_Item_Part extends Zend_Db_Table_Row_Abstract{
    protected $_table = 'ItemPart';
    protected $_tableClass = 'Application_Model_Item_Part_Table';
}