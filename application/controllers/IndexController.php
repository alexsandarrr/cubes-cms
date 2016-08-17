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
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPages = $cmsSitemapPagesDbTable->search (array(
            'filters' => array (
                'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                'type' => 'ServicesPage'
            ),
            'limit' => 1
        ));
        
        $sitemapPageId = $sitemapPages[0]['id'];
        
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
        $this->view->sitemapPageId = $sitemapPageId;
    }

    public function testAction()
    {
        
    }
    
}

