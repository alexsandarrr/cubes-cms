<?php

class PhotogalleriesController extends Zend_Controller_Action
{
    public function indexAction () {
        
        $request = $this->getRequest();
        
        $sitemapPageId = (int) $request->getParam('sitemap_page_id');

        if ($sitemapPageId <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $sitemapPageId, 404);
        }

        $cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();

        $sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);

        if (!$sitemapPage) {
            throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $sitemapPageId, 404);
        }

        if (
                $sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
                //check if user is not logged in
                //then preview is not available
                //for disabled pages
                && !Zend_Auth::getInstance()->hasIdentity()
        ) {
            throw new Zend_Controller_Router_Exception('Sitemap page is disabled', 404);
        }
        
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
        
        $photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
            'filters' => array(
                'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
        ));
        
        $this->view->photoGalleries = $photoGalleries;
        $this->view->sitemapPage = $sitemapPage;
    }
    
    public function galleryAction () {
        
        $request = $this->getRequest();
        
        $sitemapPageId = (int) $request->getParam('sitemap_page_id');

        if ($sitemapPageId <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $sitemapPageId, 404);
        }

        $cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();

        $sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);

        if (!$sitemapPage) {
            throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $sitemapPageId, 404);
        }

        if (
                $sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
                //check if user is not logged in
                //then preview is not available
                //for disabled pages
                && !Zend_Auth::getInstance()->hasIdentity()
        ) {
            throw new Zend_Controller_Router_Exception('Sitemap page is disabled', 404);
        }
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid photoGallery id: ' . $id, 404);
        }
        
        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
                
        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
        
        if (empty($photoGallery)) {
            throw new Zend_Controller_Router_Exception('No photo Gallery is found with id: ' . $id, 404);
        }
        
        $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
        
        $photos = $cmsPhotosDbTable->search(array (
            'filters' => array (
                'photo_gallery_id' => $photoGallery['id']
            ),
            'orders' => array (
                'order_number' => 'ASC'
            )
        ));
        
        $this->view->photoGallery = $photoGallery;
        $this->view->photos = $photos;
        $this->view->sitemapPage = $sitemapPage;
    }
}

