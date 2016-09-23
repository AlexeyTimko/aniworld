<?php
/**
 * Created by PhpStorm.
 * User: Timko
 * Date: 25.04.14
 * Time: 13:22
 */

class Application_Model_Parser {
    public $errors = array();
    protected $_links = array();
    public function parseAll($url){
//        return;
        ini_set("memory_limit", "512M");
        ini_set('max_execution_time', 0);
        ignore_user_abort(true);
        set_time_limit(0);
        $this->_gatherLinks($url);
        $this->_links = array_reverse($this->_links);
        foreach($this->_links as $link){
            $this->parseUrl($link);
        }
    }
    public function parseUrl($url){
        $page = file_get_contents($url);
        if(!preg_match('/<h1 .*titlfull.*>(.*)\[(\d+) из (.*)\]/ui',$page,$match)){
            if(!preg_match('/<h1 .*titlfull.*>(.*)\[(.*)(.*)\]/ui',$page,$match)){
                if(!preg_match('/<h1 .*titlfull.*>(.*)<\/h1/ui',$page,$match)){
                    $this->errors[] = 'Cтруктура сайта поменялась!!!(Название) - <a href="'.$url.'">'.$url.'</a>';
                    return false;
                }else{
                    $TotalParts = 1;
                }
            }
        }
        $Name = trim($match[1]);
        $itemTable = new Application_Model_Item_Table();
        $item = $itemTable->fetchRow(array('Name = ?' => $Name));
        if(is_null($item)){
            if(!isset($TotalParts)){
                $TotalParts = is_numeric(trim($match[3]))?trim($match[3]):(empty($match[3])?1:0);
            }
            if(!preg_match('/Описание<\/b>[^:]*:(.*)class=\"clear\"/ui',$page,$match)){
                $this->errors[] = 'Cтруктура сайта поменялась!!!(Описание) - <a href="'.$url.'">'.$url.'</a>';
                return false;
            }
            $Description = trim(strip_tags($match[1]));
            if(!preg_match('/poster_img.*src=\"(.*\.jpg)/ui',$page,$match)){
                $this->errors[] = 'Cтруктура сайта поменялась!!!(Картинка) - <a href="'.$url.'">'.$url.'</a>';
                return false;
            }
            $iName = time().basename($match[1]);
            if(!file_put_contents(IMAGES_PATH.'/anime/'.$iName, file_get_contents($match[1]))){
                $this->errors[] = 'Неудалось загрузить картинку!!! - <a href="'.$url.'">'.$url.'</a>';
                return false;
            }
            if(!preg_match('/<span .*genre.*\/span>/ui',$page,$match)){
                $this->errors[] = 'Cтруктура сайта поменялась!!!(Жанр) - <a href="'.$url.'">'.$url.'</a>';
                return false;
            }
            $genres = explode(',', strip_tags($match[0]));
            foreach($genres as $k => $genre){
                $genres[$k] = trim($genre);
            }
            $genreTable = new Application_Model_Genre_Table();
            $genres = $genreTable->fetchAll(array(
                'Name IN (?)' => $genres,
            ));
            $item = $itemTable->createRow(array(
                'Name' => $Name,
                'Date' => date("Y-m-d H:i:s"),
                'Description' => $Description,
                'Image' => $iName,
                'Type' => 1,
                'TotalParts' => $TotalParts,
            ));
            $id = $item->save();
            $itemGenreTable = new Application_Model_Item_Genre_Table();
            foreach($genres as $gid){
                $itemGenreTable->createRow(array(
                    'ItemId' => $id,
                    'GenreId' => $gid->Id,
                ))->save();
            }
            $apdate = false;
        }else{
            $id = $item->Id;
            $apdate = true;
        }
        $apdateItemDate = false;
        $itemPartTable = new Application_Model_Item_Part_Table();
        $iframe = '<iframe src="%s"></iframe>';
        if(!preg_match_all('/value=[\"\']{1}(http:\/\/(vk\.com|video\.sibnet|www\.now|pp\.anidub\-online\.ru)[^\|]*)\|(\d+)/ui',$page,$matchParts)){
            if(!$apdate || is_null($itemPartTable->fetchRow(array('ItemId = ?'=>$id)))){
                if(!preg_match('/src=[\"\']{1}(http:\/\/(vk\.com|video\.sibnet|www\.now|pp\.anidub\-online\.ru)[^\"\']*)/ui',$page,$match)){
                    $this->errors[] = 'Cтруктура сайта поменялась!!!(Видео) - <a href="'.$url.'">'.$url.'</a>';
                    return false;
                }
                $itemPartTable->createRow(array(
                    'ItemId' => $id,
                    'Part' => 1,
                    'Name' => $Name,
                    'Object' => sprintf($iframe, $match[1]),
                ))->save();
            }
        }else{
            foreach($matchParts[1] as $k => $href){
                $part = $matchParts[3][$k];
                if(!$apdate || is_null($itemPartTable->fetchRow(array('ItemId = ?'=>$id,'Part = ?'=>$part)))){
                    try{
                        $itemPartTable->createRow(array(
                            'ItemId' => $id,
                            'Part' => $part,
                            'Name' => $Name,
                            'Object' => sprintf($iframe, $href),
                        ))->save();
                    }catch (Exception $e){
                        $this->errors[] = $e->getMessage().$url;
                    }
                    $apdateItemDate = true;
                }
            }
        }
        if($apdateItemDate){
            $item->Date = date("Y-m-d H:i:s");
            $item->save();
        }
        return true;
    }
    protected function _gatherLinks($url){
        $page = 1;
        $url = rtrim($url, '/');
        while($content = @file_get_contents($url.'/page/'.$page)){
            if(!preg_match_all('/itemprop="name"[^h]*href=[\'\"](http:\/\/online\.anidub\.com\/anime[^\"\']+)[\'\"]/ui', $content, $match)){
                $this->errors[] = 'Не найдены ссылки на странице = '.$url.'/page/'.$page;
            }else{
                foreach($match[1] as $link){
                    $this->_links[] = $link;
                }
            }
            $page++;
        }
    }
} 