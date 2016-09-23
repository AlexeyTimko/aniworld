<?php

class AbstractController extends Zend_Controller_Action
{
    /**
     * @var Zend_Auth null
     */
    public $auth = null;
    public function init()
    {
        $this->view->auth = $this->auth = Zend_Auth::getInstance();
        $itemTable = new Application_Model_Item_Table();
        $offset = Zend_Registry::get('db')->fetchRow('SELECT FLOOR(RAND() * COUNT(*)) AS `offset` FROM `Item`');
        $this->view->randomItem = $itemTable->fetchRow(array(), 'Id ASC', $offset->offset);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial ( 'paginator.phtml' );
        $genreTable = new Application_Model_Genre_Table();
        $this->view->genres = array();
        $genres = $genreTable->fetchAll(null, 'Order ASC');
        foreach($genres as $genre){
            $this->view->genres[$genre->Id] = $genre;
        }
        $this->view->isAdmin = Zend_Auth::getInstance()->hasIdentity() && Zend_Auth::getInstance()->getIdentity()->User->Admin;
    }
    public function paginatorPrepare($select, $itemsPerPage = 10){
        $paginator = Zend_Paginator::factory($select);

        $page = $this->_request->getParam('page', 1);

        $paginator->setItemCountPerPage($itemsPerPage);
        $paginator->setCurrentPageNumber($page);

        $this->view->paginator = $paginator;
    }
    protected function _setTitle($t){
        $title = ' Смотреть аниме онлайн anime online';
        $this->view->title = $t.$title;
    }
}

