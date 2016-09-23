<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:10
 */

class Application_Model_Rate_Table extends Zend_Db_Table_Abstract{
    protected $_rowClass = 'Application_Model_Rate';
    protected $_name = 'Rate';
    public function getRate($itemIds){
        if(empty($itemIds)) return null;
        $select = $this->getAdapter()->select()->from($this->_name, array(
            'ItemId',
            'Total' => 'COUNT(*)',
            'Rate' => 'ROUND(SUM(Mark)/COUNT(*), 2)',
        ))->where('ItemId IN (?)', $itemIds)->group('ItemId');

        return $this->getAdapter()->fetchAll($select);
    }
    public function isVoted($id){
        if(!Zend_Auth::getInstance()->hasIdentity()){
            return true;
        }
        $row = $this->fetchRow(array(
            'ItemId = ?' => $id,
            'UserId = ?' => Zend_Auth::getInstance()->getIdentity()->User->Id,
        ));
        return is_null($row)?false:true;
    }
}