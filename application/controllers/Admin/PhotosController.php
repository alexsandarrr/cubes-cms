<?php

class Admin_PhotosController extends Zend_Controller_Action
{
    public function addAction () {
        
        $request = $this->getRequest();
        
        $photoGalleryId = (int) $request->getParam('photo_gallery_id');
        
        if ($photoGalleryId <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid photoGallery id: ' . $photoGalleryId, 404);
        }
        
        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
                
        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($photoGalleryId);
        
        if (empty($photoGallery)) {
            throw new Zend_Controller_Router_Exception('No photo Gallery is found with id: ' . $photoGalleryId, 404);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_PhotoAdd();

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
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new photo');
                }

                //get form data
                $formData = $form->getValues();
                
                // remove key photo_upload from form data because there is no column 'photo_upload' in cms_photos table
                unset($formData['photo_upload']);
                
                $formData['photo_gallery_id'] = $photoGallery['id'];
                
                $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                
                // insert photo returns ID of the new photo
                $photoId = $cmsPhotosTable->insertPhoto($formData);
                
                if ($form->getElement('photo_upload')->isUploaded()) {
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('photo_upload')->getFileInfo('photo_upload');
                    $fileInfo = $fileInfos['photo_upload'];
                    
                    try {
                        // open uploaded photo in temporary directory
                        $photoPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $photoPhoto->fit(660, 495);
                        
                        $photoPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/photos/' . $photoId . '.jpg');
                        
                    } catch (Exception $ex) {
                        
                        $flashMessenger->addMessage('Photo has been saved but error occured during image processing', 'errors');

                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_photogalleries',
                                    'action' => 'edit',
                                    'id' => $photoGalleryId
                                        ), 'default', true);
                    }
                    
                    //$fileInfo = $_FILES['photo_upload']; moze i ovako
                }

                // do actual task
                // save to database etc
                
                
                
                // set system message
                $flashMessenger->addMessage('Photo has been saved', 'success');

                // redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photoGallery['id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                $flashMessenger->addMessage($ex->getMessage(), 'errors');

                // redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photoGalleryId
                                ), 'default', true);
            }
        }
        
        $redirector = $this->getHelper('Redirector');
        $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_photogalleries',
                    'action' => 'edit',
                    'id' => $photoGalleryId
                        ), 'default', true);
                
    }
    
    public function editAction () {
        
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid photo id: ' . $id, 404);
        }
        
        $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                
        $photo = $cmsPhotosTable->getPhotoById($id);
        
        if (empty($photo)) {
            throw new Zend_Controller_Router_Exception('No photo is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_PhotoEdit();

        //default form data
        $form->populate($photo);

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                // check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for photo');
                }

                // get form data
                $formData = $form->getValues();
                
                // radimo update postojeceg zapisa u tabeli
                $cmsPhotosTable->updatePhoto($photo['id'], $formData);

                // do actual task
                // save to database etc
                
                // set system message
                $flashMessenger->addMessage('Photo has been updated', 'success');

                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photo['photo_gallery_id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                $flashMessenger->addMessage($ex->getMessage(), 'errors');

                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photo['photo_gallery_id']
                                ), 'default', true);
            }
        }

        $flashMessenger->addMessage($ex->getMessage(), 'errors');

        $redirector = $this->getHelper('Redirector');
        $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_photogalleries',
                    'action' => 'edit',
                    'id' => $photo['photo_gallery_id']
                        ), 'default', true);
    }
    
    public function deleteAction () {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            // request is not post
            // or task is not delete
            // redirect to index page
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
            
        }
        
        $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                
        $photo = $cmsPhotosTable->getPhotoById($id);
        
        if (empty($photo)) {
            
            throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id);
            
        }
        
        $cmsPhotosTable->deletePhoto($id);
        
        $flashMessenger->addMessage('Photo has been deleted', 'success');

        $redirector = $this->getHelper('Redirector');
        $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_photogalleries',
                    'action' => 'edit',
                    'id' => $photo['photo_gallery_id']
                        ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        }
        
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
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
            
        }
        
        $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                
        $photo = $cmsPhotosTable->getPhotoById($id);
        
        if (empty($photo)) {
            
            throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id);
            
        }
        
        $cmsPhotosTable->disablePhoto($id);
        
        $flashMessenger->addMessage('Photo has been disabled', 'success');
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'edit',
                        'id' => $photo['photo_gallery_id']
                            ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
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
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                                ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
            
        }
        
        $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                
        $photo = $cmsPhotosTable->getPhotoById($id);
        
        if (empty($photo)) {
            
            throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id);
            
        }
        
        $cmsPhotosTable->enablePhoto($id);
        
        $flashMessenger->addMessage('Photo has been enabled', 'success');
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'edit',
                        'id' => $photo['photo_gallery_id']
                            ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                                ), 'default', true);
        }
        
    }
    
    public function updateorderAction () {
        
        $request = $this->getRequest();
        
        $photoGalleryId = (int) $request->getParam('photo_gallery_id');
        
        if ($photoGalleryId <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid Photo Gallery id: ' . $photoGalleryId, 404);
        }
        
        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
                
        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($photoGalleryId);
        
        if (empty($photoGallery)) {
            throw new Zend_Controller_Router_Exception('No photo Gallery is found with id: ' . $photoGalleryId, 404);
        }
        
        if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
            // request is not post
            // or task is not saveOrder
            // redirect to index page
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            
            $sortedIds = $request->getPost('sorted_ids');
            
            if(empty($sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Sortered ids are not sent');
            }
            
            $sortedIds = trim($sortedIds, ' ,');
            
            if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }
            
            $sortedIds = explode(',', $sortedIds);
            
            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
            
            $cmsPhotosTable->updateOrderOfPhotos($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photoGallery['id']
                                ), 'default', true);
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index',
                            ), 'default', true);
        
        }
        
    }
}

