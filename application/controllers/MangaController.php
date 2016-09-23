<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:37
 */

class MangaController extends AbstractController{
    public function indexAction(){
        $itemTable = new Application_Model_Item_Table();
        $select = $itemTable->getAdapter()->select()->from('Item')->where('Type = 2')->order('Date Desc');
        if($genre = $this->getRequest()->getParam('genre')){
            $select->join('ItemGenre', "ItemGenre.ItemId = Item.Id AND ItemGenre.GenreId = $genre");
            $this->view->genre = $genre;
        }
        if($search = $this->getRequest()->getParam('search')){
            $select->where('Item.Name LIKE ?', "%$search%");
        }
        $this->paginatorPrepare($select);
        $itemPartTable = new Application_Model_Item_Part_Table();
        $ids = array();
        foreach($this->view->paginator as $item){
            $ids[] = $item->Id;
        }
        $this->view->partCount = array();
        if(!empty($ids)){
            $select = $itemPartTable->select()
                ->from($itemPartTable, array('ItemId','count' => 'COUNT(*)'))
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
    public function storyAction(){
        $id = $this->getRequest()->getParam('id');
        $part = $this->getRequest()->getParam('part', 1);
        if(!$id){
            throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
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
    }
} 