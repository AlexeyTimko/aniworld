<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:37
 */

class AnimeController extends AbstractController{
    public function indexAction(){
        $itemTable = new Application_Model_Item_Table();
        $select = $itemTable->getAdapter()->select()->from('Item')->where('Type = 1')->group('Item.Id');
        if($genre = $this->getRequest()->getParam('genre')){
            $select->join('ItemGenre', "ItemGenre.ItemId = Item.Id AND ItemGenre.GenreId = $genre");
            $this->view->genre = $genre;
        }
        if($sort = $this->getRequest()->getParam('sort')){
            if($sort == 'popular'){
                $select->joinLeft('Rate', "Rate.ItemId = Item.Id", array())->order('ROUND(SUM(Rate.Mark)/COUNT(*), 2) DESC');
            }
        }
        if($search = $this->getRequest()->getParam('search')){
            $select->where('Item.Name LIKE ?', "%$search%");
        }
        $select->order('Date Desc');
        $this->paginatorPrepare($select);
        $itemPartTable = new Application_Model_Item_Part_Table();
        $ids = array();
        foreach($this->view->paginator as $item){
            $ids[] = $item->Id;
        }
        $this->view->partCount = array();
        if(!empty($ids)){
            $select = $itemPartTable->select()
                ->from($itemPartTable, array('ItemId','count' => 'MAX(Part)'))
                ->where('ItemId IN (?)', $ids)
                ->group('ItemId');
            $this->view->partCount = $itemPartTable->getAdapter()->fetchPairs($select);
            $itemGenreTable = new Application_Model_Item_Genre_Table();
            $select = $itemGenreTable->select()
                ->from($itemGenreTable, array('ItemId','Genres' => 'GROUP_CONCAT(GenreId)'))
                ->where('ItemId IN (?)', $ids)
                ->group('ItemId');
            $this->view->itemGenres = $itemPartTable->getAdapter()->fetchPairs($select);
            foreach($this->view->itemGenres as $id => $genres){
                $this->view->itemGenres[$id] = explode(',', $genres);
            }
            $rateTable = new Application_Model_Rate_Table();
            $this->view->rate = array();
            $rate = $rateTable->getRate($ids);
            foreach($rate as $row){
                $this->view->rate[$row->ItemId] = $row;
            }
        }
    }
    public function movieAction(){
        $id = $this->getRequest()->getParam('id');
        $part = $this->getRequest()->getParam('part');
        if(!$id){
            throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
        }
        $userItemTable = new Application_Model_User_Item_Table();
        if($this->auth->hasIdentity()){
            $row = $userItemTable->fetchRow(array(
                'UserId = ?' => $this->auth->getIdentity()->User->Id,
                'ItemId = ?' => $id,
            ));
        }
        if(is_null($part) && $this->auth->hasIdentity()){
            if(!is_null($row)){
                $part = $row->Part;
            }else{
                $part = 1;
            }
        }elseif($this->auth->hasIdentity()){
            if(!is_null($row)){
                $row->Part = $part;
            }else{
                $row = $userItemTable->createRow(array(
                    'UserId' => $this->auth->getIdentity()->User->Id,
                    'ItemId' => $id,
                    'Part' => $part,
                ));
            }
            $row->save();
        }
        if(is_null($part)){
            $part = 1;
        }
        $itemTable = new Application_Model_Item_Table();
        $this->view->item = $itemTable->fetchRow(array('Id = ?' => $id));
        $itemPartsTable = new Application_Model_Item_Part_Table();
        $this->view->parts = $itemPartsTable->fetchAll(array('ItemId = ?' => $id));
        foreach($this->view->parts as $p){
            if($p->Part == $part){
                $this->view->object = $p->Object;
                $this->view->part = $p->Part;
                break;
            }
        }
        $itemGenreTable = new Application_Model_Item_Genre_Table();
        $this->view->itemGenres = $itemGenreTable->fetchAll(array(
            'ItemId = ?' => $id
        ));
        $rateTable = new Application_Model_Rate_Table();
        $this->view->rate = current($rateTable->getRate($id));
        $this->view->canVote = !$rateTable->isVoted($id);
        $this->_setTitle($this->view->item->Name.' '.$part.' серия');
    }
    public function voteAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->getRequest()->getParam('id');
        $mark = $this->getRequest()->getParam('mark');
        if(!$id || !$mark || !$this->getRequest()->isPost() || !$this->auth->hasIdentity()){
            throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
        }
        $rateTable = new Application_Model_Rate_Table();
        if(!$rateTable->isVoted($id)){
            $rateTable->createRow(array(
                'ItemId' => $id,
                'UserId' => $this->auth->getIdentity()->User->Id,
                'Mark' => $mark,
            ))->save();
            print 'true';
        }
    }
} 