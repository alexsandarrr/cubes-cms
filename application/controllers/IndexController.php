<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        
        // $select je objekat klase Zend_Db_Select
        $services = $cmsServicesDbTable->search (array(
            'filters' => array (
                'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED,
            ),
            'orders' => array (
                'order_number' => 'ASC'
            ),
            'limit' => 4
        ));
        
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
        
        // $select je objekat klase Zend_Db_Select
        $photoGalleries = $cmsPhotoGalleriesDbTable->search (array(
            'filters' => array (
                'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED,
            ),
            'orders' => array (
                'order_number' => 'ASC'
            ),
            'limit' => 4
        ));
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapServicesPages = $cmsSitemapPagesDbTable->search (array(
            'filters' => array (
                'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                'type' => 'ServicesPage'
            ),
            'limit' => 1
        ));
        
        $sitemapPhotoGallerisePages = $cmsSitemapPagesDbTable->search (array(
            'filters' => array (
                'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                'type' => 'PhotoGalleriesPage'
            ),
            'limit' => 1
        ));
        
        $sitemapServicesPageId = $sitemapServicesPages[0]['id'];
        $sitemapPhotoGallerisePageId = $sitemapPhotoGallerisePages[0]['id'];
        
        $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
        
        $indexSlides = $cmsIndexSlidesDbTable->search (array(
            'filters' => array (
                'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED,
            ),
            'orders' => array (
                'order_number' => 'ASC'
            ),
        ));
        
        
        $this->view->services = $services;
        $this->view->indexSlides = $indexSlides;
        $this->view->sitemapServicesPageId = $sitemapServicesPageId;
        $this->view->sitemapPhotoGallerisePageId = $sitemapPhotoGallerisePageId;
        $this->view->photoGalleries = $photoGalleries;
    }

    public function testAction()
    {
        
    }
    
}

