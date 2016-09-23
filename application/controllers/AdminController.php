<?php
/**
 * Created by PhpStorm.
 * User: Timko
 * Date: 22.04.14
 * Time: 11:08
 */

class AdminController extends AbstractController{
    public function init(){
        parent::init();
        if(!$this->view->isAdmin){
            $this->_helper->redirector('index', 'index');
        }
    }
    public function addItemAction(){
        $itemTable = new Application_Model_Item_Table();
        $fields = $itemTable->info(Application_Model_Item_Table::METADATA);
        $this->view->fields = $fields;
        if($this->getRequest()->isPost()){
            $iName = time().$_FILES['Image']['name'];
            move_uploaded_file($_FILES['Image']['tmp_name'], IMAGES_PATH.'/anime/'.$iName);
            $item = $itemTable->createRow(array(
                'Name' => $this->getRequest()->getParam('Name'),
                'Date' => date("Y-m-d H:i:s"),
                'Description' => $this->getRequest()->getParam('Description'),
                'Image' => $iName,
                'Type' => $this->getRequest()->getParam('Type'),
                'TotalParts' => $this->getRequest()->getParam('TotalParts'),
            ));
            $id = $item->save();
            $itemGenreTable = new Application_Model_Item_Genre_Table();
            foreach($this->getRequest()->getParam('Genres') as $gid){
                $itemGenreTable->createRow(array(
                    'ItemId' => $id,
                    'GenreId' => $gid,
                ))->save();
            }
            $this->_helper->redirector('index', 'index');
        }
    }
    public function addItemPartAction(){
        if(!($id = $this->getRequest()->getParam('id'))){
            $this->_helper->redirector('index', 'index');
        }
        $itemPartTable = new Application_Model_Item_Part_Table();
        $fields = $itemPartTable->info(Application_Model_Item_Part_Table::METADATA);
        $this->view->fields = $fields;
        $itemTable = new Application_Model_Item_Table();
        $item = $itemTable->fetchRow(array('Id = ?' => $id));
        $this->view->item = $item;
        $select = $itemPartTable->select()
            ->from($itemPartTable, array('count' => 'COUNT(*)'))
            ->where('ItemId = ?', $id)
            ->group('ItemId');
        $this->view->partCount = $itemPartTable->getAdapter()->fetchOne($select);
        if($this->getRequest()->isPost()){
            $part = $itemPartTable->fetchRow(array('ItemId = ?' => $id, 'Part = ?' => $this->getRequest()->getParam('Part')));
            if(!is_null($part)){
                $this->view->error = 'Серия уже существует';
                return;
            }
            $itemPart = $itemPartTable->createRow(array(
                'ItemId' => $id,
                'Part' => $this->getRequest()->getParam('Part'),
                'Name' => $this->getRequest()->getParam('Name'),
                'Object' => $this->getRequest()->getParam('Object'),
            ));
            $itemPart->save();
            $item->Date = date("Y-m-d H:i:s");
            $item->save();
            $this->_helper->redirector('edit-item', 'admin', null, array('id' => $id));
        }
    }
    public function editItemPartAction(){
        if(!($id = $this->getRequest()->getParam('id')) || !($part = $this->getRequest()->getParam('part'))){
            $this->_helper->redirector('index', 'index');
        }
        $itemPartTable = new Application_Model_Item_Part_Table();
        $fields = $itemPartTable->info(Application_Model_Item_Part_Table::METADATA);
        $this->view->fields = $fields;
        $this->view->part = $part = $itemPartTable->fetchRow(array('ItemId = ?' => $id, 'Part = ?' => $part));
        if(is_null($part)){
            $this->_helper->redirector('edit-item', 'admin', null, array('id' => $id));
        }
        $itemTable = new Application_Model_Item_Table();
        $item = $itemTable->fetchRow(array('Id = ?' => $id));
        $this->view->item = $item;
        $select = $itemPartTable->select()
            ->from($itemPartTable, array('count' => 'COUNT(*)'))
            ->where('ItemId = ?', $id)
            ->group('ItemId');
        $this->view->partCount = $itemPartTable->getAdapter()->fetchOne($select);
        if($this->getRequest()->isPost()){
            $part->setFromArray(array(
                'Name' => $this->getRequest()->getParam('Name'),
                'Object' => $this->getRequest()->getParam('Object'),
            ));
            $part->save();
            $item->Date = date("Y-m-d H:i:s");
            $item->save();
            $this->_helper->redirector('edit-item', 'admin', null, array('id' => $id));
        }
    }
    public function deleteItemPartAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if(!($id = $this->getRequest()->getParam('id')) || !($part = $this->getRequest()->getParam('part'))){
            $this->_helper->redirector('index', 'index');
        }
        $itemPartTable = new Application_Model_Item_Part_Table();
        $item = $itemPartTable->fetchRow(array('ItemId = ?' => $id, 'Part = ?' => $part));
        if(!is_null($item)){
            $item->delete();
        }
        $this->_helper->redirector('edit-item', 'admin', null, array('id' => $id));
    }
    public function editItemAction(){
        if(!($id = $this->getRequest()->getParam('id'))){
            $this->_helper->redirector('index', 'index');
        }
        $itemTable = new Application_Model_Item_Table();
        $fields = $itemTable->info(Application_Model_Item_Table::METADATA);
        $this->view->fields = $fields;
        $item = $itemTable->fetchRow(array('Id = ?' => $id));
        $this->view->item = $item;
        $itemPartsTable = new Application_Model_Item_Part_Table();
        $this->view->parts = $itemPartsTable->fetchAll(array('ItemId = ?' => $id));
        $itemGenreTable = new Application_Model_Item_Genre_Table();
        $this->view->itemGenres = $itemGenreTable->getAdapter()->fetchCol($itemGenreTable->select()->from($itemGenreTable, 'GenreId')->where('ItemId = ?', $id));
        if($this->getRequest()->isPost()){
            $item->setFromArray(array(
                'Name' => $this->getRequest()->getParam('Name'),
                'Date' => date("Y-m-d H:i:s"),
                'Description' => $this->getRequest()->getParam('Description'),
                'Type' => $this->getRequest()->getParam('Type'),
                'TotalParts' => $this->getRequest()->getParam('TotalParts'),
            ));

            if(isset($_FILES['Image'])
                && ($iName = time().$_FILES['Image']['name'])
                && move_uploaded_file($_FILES['Image']['tmp_name'], IMAGES_PATH.'/anime/'.$iName)){
                $item->Image = $iName;
            }
            $item->save();
            foreach($this->getRequest()->getParam('Genres') as $gid){
                if(in_array($gid,$this->view->itemGenres)) continue;
                $itemGenreTable->createRow(array(
                    'ItemId' => $id,
                    'GenreId' => $gid,
                ))->save();
            }
            $this->_helper->redirector('index', 'index');
        }
    }
    public function deleteItemAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if(!($id = $this->getRequest()->getParam('id'))){
            $this->_helper->redirector('index', 'index');
        }
        $itemTable = new Application_Model_Item_Table();
        $item = $itemTable->fetchRow(array('Id = ?' => $id));
        if(!is_null($item)){
            unlink(IMAGES_PATH.'/anime/'.$item->Image);
            $itemTable->getAdapter()->delete('ItemPart', 'ItemId = '.$item->Id);
            $itemTable->getAdapter()->delete('ItemGenre', 'ItemId = '.$item->Id);
            $item->delete();
        }
        $this->_helper->redirector('index', 'index');
    }
    public function animeParserAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        if(!$this->getRequest()->isPost() || !($url = $this->getRequest()->getParam('url'))){
            $this->_helper->redirector('add-item', 'admin');
        }
        $parser = new Application_Model_Parser();
        $parser->parseUrl($url);
        if(empty($parser->errors)){
            print 'Ура!!! Получилось!!!<br/>Добавить еще:<br/><form method="post" action="/admin/anime-parser">
    <input type="url" name="url">
    <input type="submit" value="Автодобавление">
</form>';
        }else{
            print join('<br/>', $parser->errors);
        }
    }
    public function parseAllAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        if(!$this->getRequest()->isPost() || !($url = $this->getRequest()->getParam('url'))){
            $this->_helper->redirector('add-item', 'admin');
        }
        $parser = new Application_Model_Parser();
        $parser->parseAll($url);
        if(empty($parser->errors)){
            print 'Ура!!! Получилось!!!';
        }else{
            print join('<br/>', $parser->errors);
        }
    }
}