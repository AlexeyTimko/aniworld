<?php

class IndexController extends AbstractController
{
    public function indexAction()
    {
        $itemTable = new Application_Model_Item_Table();
        $select = $itemTable->getAdapter()->select()->from('Item')->order('Date Desc');
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
}

