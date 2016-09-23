<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:10
 */

class Application_Model_Item_Genre_Table extends Zend_Db_Table_Abstract{
    protected $_rowClass = 'Application_Model_Item_Genre';
    protected $_name = 'ItemGenre';
} 