<?php

class Admin_SitemapController extends Zend_Controller_Action
{
    public function indexAction() {
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        
        // if no request_id parameter, then $parameterId will be 0
        $id = (int) $request->getParam('id', 0); // po defaultu ce biti 0 ako ne prosledimo nista za 'parent_id'
        
        if ($id < 0) {
            throw new Zend_Controller_Router_Exception('Invalid id for sitemap pages', 404);
        }
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages;
        
        if ($id != 0) {
            
            $sitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($id);
            
            if (!$sitemapPage) {
                throw new Zend_Controller_Router_Exception('No sitemap page is found', 404);
            }
        }
        
        $childSitemapPages = $cmsSitemapPagesDbTable->search(array(
            'filters' => array(
                'parent_id' => $id,
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
            //'limit' => 50,
            //'page' => 3
        ));
        
        $sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($id);
        
        $this->view->currentSitemapPageId = $id;
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
        $this->view->childSitemapPages = $childSitemapPages;
        $this->view->systemMessages = $systemMessages;
    }
    
    public function addAction () {
        
        $request = $this->getRequest();
        
        $parentId = (int) $request->getParam('parent_id', 0);
        
        if ($parentId < 0) {
            throw new Zend_Controller_Router_Exception('Invalid id for sitemap pages', 404);
        }
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages;
        
        if ($parentId !=0) {
            // check if parent page exists
            $parentSitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($parentId);
            
            if (!$parentSitemapPage) {
                throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $parentId, 404);
            }
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_SitemapPageAdd($parentId);

        //default form data
        $form->populate(array(
            
        ));

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new sitemap page');
                }

                //get form data
                $formData = $form->getValues();
                
                $formData['parent_id'] = $parentId;
                
                // remove key sitemap_page_photo from form data because there is no column 'sitemap_page_photo' in cms_sitemapPages table
                //unset($formData['sitemap_page_photo']);
                
                
                // insert sitemapPage returns ID of the new sitemapPage
                $sitemapPageId = $cmsSitemapPagesDbTable->insertSitemapPage($formData);
                
//                if ($form->getElement('sitemap_page_photo')->isUploaded()) {
//                    // photo is uploaded
//                    
//                    $fileInfos = $form->getElement('sitemap_page_photo')->getFileInfo('sitemap_page_photo');
//                    $fileInfo = $fileInfos['sitemap_page_photo'];
//                    
//                    try {
//                        // open uploaded photo in temporary directory
//                        $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//                        
//                        $sitemapPagePhoto->fit(150, 150);
//                        
//                        $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPageId . '.jpg');
//                        
//                    } catch (Exception $ex) {
//                        
//                        $flashMessenger->addMessage('SitemapPage has been saved but error occured during image processing', 'errors');
//
//                        //redirect to same or another page
//                        $redirector = $this->getHelper('Redirector');
//                        $redirector->setExit(true)
//                                ->gotoRoute(array(
//                                    'controller' => 'admin_sitemapPages',
//                                    'action' => 'edit',
//                                    'id' => $sitemapPageId
//                                        ), 'default', true);
//                    }
//                    
//                    //$fileInfo = $_FILES['sitemap_page_photo']; moze i ovako
//                }

                // do actual task
                //save to database etc
                
                
                
                //set system message
                $flashMessenger->addMessage('Sitemap page has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $parentId
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        
        $sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($parentId);
        
        $this->view->parentId = $parentId;
        $this->view->systemMessages = $systemMessages;
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
        $this->view->form = $form;
    }
    
    public function editAction () {
        
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $id, 404);
        }
        
        $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
                
        $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
        
        if (empty($sitemapPage)) {
            throw new Zend_Controller_Router_Exception('No sitemap page is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_SitemapPageEdit($sitemapPage['id'], $sitemapPage['parent_id']);

        //default form data
        $form->populate($sitemapPage);

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                // check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for sitemap page');
                }

                // get form data
                $formData = $form->getValues();
                
//                unset($formData['sitemapPage_photo']);
//                
//                if ($form->getElement('sitemapPage_photo')->isUploaded()) {
//                    // photo is uploaded
//                    
//                    $fileInfos = $form->getElement('sitemapPage_photo')->getFileInfo('sitemapPage_photo');
//                    $fileInfo = $fileInfos['sitemapPage_photo'];
//                    
//                    try {
//                        // open uploaded photo in temporary directory
//                        $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//                        
//                        $sitemapPagePhoto->fit(150, 150);
//                        
//                        $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPage['id'] . '.jpg');
//                        
//                    } catch (Exception $ex) {
//                        
//                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
//                        
//                    }
//                    
//                    //$fileInfo = $_FILES['sitemapPage_photo']; moze i ovako
//                }
//                
                // radimo update postojeceg zapisa u tabeli
                $cmsSitemapPagesTable->updateSitemapPage($sitemapPage['id'], $formData);

                // do actual task
                // save to database etc
                
                // set system message
                $flashMessenger->addMessage('SitemapPage has been updated', 'success');

                // redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        
        $sitemapPageBreadcrumbs = $cmsSitemapPagesTable->getSitemapPageBreadcrumbs($sitemapPage['parent_id']);
        
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
        $this->view->sitemapPage = $sitemapPage;
    }
    
        public function disableAction () {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'disable') {
            // request is not post
            // or task is not delete
            // redirect to index page
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid sitemap page id: ' . $id);
            
        }
        
        $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
                
        $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
        
        if (empty($sitemapPage)) {
            
            throw new Application_Model_Exception_InvalidInput('No sitemap page is found with id: ' . $id);
            
        }
        
        $cmsSitemapPagesTable->disableSitemapPage($id);
        
        $flashMessenger->addMessage('Sitemap page ' . $sitemapPage['first_name'] . ' ' . $sitemapPage['last_name'] . ' has been disabled', 'success');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
        }
        
    }

    public function enableAction () {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'enable') {
            // request is not post
            // or task is not delete
            // redirect to index page
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid sitemap page id: ' . $id);
            
        }
        
        $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
                
        $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
        
        if (empty($sitemapPage)) {
            
            throw new Application_Model_Exception_InvalidInput('No sitemap page is found with id: ' . $id);
            
        }
        
        $cmsSitemapPagesTable->enableSitemapPage($id);
        
        $flashMessenger->addMessage('Sitemap page ' . $sitemapPage['first_name'] . ' ' . $sitemapPage['last_name'] . ' has been enabled', 'success');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
        }
        
    }
}

