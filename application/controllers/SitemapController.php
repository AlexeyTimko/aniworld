<?php
/**
 * Created by PhpStorm.
 * User: Timko
 * Date: 22.04.14
 * Time: 11:08
 */

class SitemapController extends AbstractController{
    public function indexAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        header("Content-type: text/xml; charset=utf-8");
        print '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
        print ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        print '<url><loc>http://ani-world.ru/</loc><changefreq>always</changefreq><priority>1.00</priority></url>';
        print '<url><loc>http://ani-world.ru/anime/</loc><changefreq>always</changefreq><priority>1.00</priority></url>';
        print '<url><loc>http://ani-world.ru/anime/index/sort/popular/</loc><changefreq>always</changefreq><priority>1.00</priority></url>';
        $genreTable = new Application_Model_Genre_Table();
        $genres = $genreTable->fetchAll();
        foreach($genres as $genre){
            printf('<url><loc>http://ani-world.ru/index/index/genre/%d/</loc><changefreq>always</changefreq><priority>0.90</priority></url>', $genre->Id);
            printf('<url><loc>http://ani-world.ru/anime/index/genre/%d/</loc><changefreq>always</changefreq><priority>0.90</priority></url>', $genre->Id);
            printf('<url><loc>http://ani-world.ru/anime/index/genre/%d/sort/popular/</loc><changefreq>always</changefreq><priority>0.90</priority></url>', $genre->Id);
        }
        $itemTable = new Application_Model_Item_Table();
        $itemPartsTable = new Application_Model_Item_Part_Table();
        $items = $itemTable->fetchAll();
        foreach($items as $item){
            printf('<url><loc>http://ani-world.ru/anime/movie/id/%d/</loc><changefreq>always</changefreq><priority>0.80</priority></url>', $item->Id);
            $parts = $itemPartsTable->fetchAll(array('ItemId = ?' => $item->Id));
            foreach($parts as $part){
                printf('<url><loc>http://ani-world.ru/anime/movie/id/%d/part/%d</loc><changefreq>always</changefreq><priority>0.70</priority></url>', $item->Id, $part->Part);
            }

        }
        print '</urlset>';
    }
}