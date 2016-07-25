<?php

class Admin_ClientsController extends Zend_Controller_Action
{
    public function indexAction () {
        
        $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
        
        $select = $cmsClientsDbTable->select();
        
        $select->order('order_number');
        
        $clients = $cmsClientsDbTable->fetchAll($select);
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        
        $this->view->clients = $clients;
        $this->view->systemMessages = $systemMessages;
    }
    
    public function addAction () {
        
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_ClientAdd();

        $form->populate(array(
            
        ));

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'saveClient') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new client');
                }

                //get form data
                $formData = $form->getValues();
                
                unset($formData['client_photo']);
                
                $cmsClientsTable = new Application_Model_DbTable_CmsClients();
                
                // insert client returns ID of the new client
                $clientId = $cmsClientsTable->insertClient($formData);
                
                if ($form->getElement('client_photo')->isUploaded()) {
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('client_photo')->getFileInfo('client_photo');
                    $fileInfo = $fileInfos['client_photo'];
                    
                    try {
                        // open uploaded photo in temporary directory
                        $clientPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $clientPhoto->fit(170, 70);
                        
                        $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $clientId . '.jpg');
                        
                    } catch (Exception $ex) {
                        
                        $flashMessenger->addMessage('Client has been saved but error occured during image processing', 'errors');

                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_clients',
                                    'action' => 'edit',
                                    'id' => $clientId
                                        ), 'default', true);
                    }
                    
                    //$fileInfo = $_FILES['client_photo']; moze i ovako
                }

                // do actual task
                //save to database etc
                
                
                
                //set system message
                $flashMessenger->addMessage('Client has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }
    
    public function editAction () {
        
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid client id: ' . $id, 404);
        }
        
        $cmsClientsTable = new Application_Model_DbTable_CmsClients();
                
        $client = $cmsClientsTable->getClientById($id);
        
        if (empty($client)) {
            throw new Zend_Controller_Router_Exception('No client is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_ClientAdd();
        $form->getElement('client_photo')->setRequired(false);

        //default form data
        $form->populate($client);

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'updateClient') {

            try {

                // check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for client');
                }

                // get form data
                $formData = $form->getValues();
                
                unset($formData['client_photo']);
                
                if ($form->getElement('client_photo')->isUploaded()) {
                    // photo is uploaded
                    
                    $fileInfos = $form->getElement('client_photo')->getFileInfo('client_photo');
                    $fileInfo = $fileInfos['client_photo'];
                    
                    try {
                        // open uploaded photo in temporary directory
                        $clientPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        
                        $clientPhoto->fit(170, 70);
                        
                        $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $client['id'] . '.jpg');
                        
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                        
                    }
                }
                
                // radimo update postojeceg zapisa u tabeli
                $cmsClientsTable->updateClient($client['id'], $formData);

                // do actual task
                // save to database etc
                
                // set system message
                $flashMessenger->addMessage('Client has been updated', 'success');

                // redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        
        $this->view->client = $client;
    }
    
    public function deleteAction () {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'deleteClient') {
            // request is not post
            // or task is not delete
            // redirect to index page
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id);
            
        }
        
        $cmsClientsTable = new Application_Model_DbTable_CmsClients();
                
        $client = $cmsClientsTable->getClientById($id);
        
        if (empty($client)) {
            
            throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id);
            
        }
        
        $cmsClientsTable->deleteClient($id);
        
        $flashMessenger->addMessage('Client ' . $client['title'] . ' has been deleted', 'success');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        }
        
    }
    
    public function disableAction () {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'disableClient') {
            // request is not post
            // or task is not delete
            // redirect to index page
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id);
            
        }
        
        $cmsClientsTable = new Application_Model_DbTable_CmsClients();
                
        $client = $cmsClientsTable->getClientById($id);
        
        if (empty($client)) {
            
            throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id);
            
        }
        
        $cmsClientsTable->disableClient($id);
        
        $flashMessenger->addMessage('Client ' . $client['title'] . ' has been disabled', 'success');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        }
        
    }

    public function enableAction () {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'enableClient') {
            // request is not post
            // or task is not delete
            // redirect to index page
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_client',
                            'action' => 'index'
                                ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            // read $_POST['id]
        $id = (int) $request->getPost('id');
        
        if ($id <= 0) {
            
            throw new Application_Model_Exception_InvalidInput('Invalid servuce id: ' . $id);
            
        }
        
        $cmsClientsTable = new Application_Model_DbTable_CmsClients();
                
        $client = $cmsClientsTable->getClientById($id);
        
        if (empty($client)) {
            
            throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id);
            
        }
        
        $cmsClientsTable->enableClient($id);
        
        $flashMessenger->addMessage('Client ' . $client['title'] . ' has been enabled', 'success');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        
    
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        }
        
    }
    
    public function updateorderAction () {
        
        $request = $this->getRequest();
        
        if (!$request->isPost() || $request->getPost('task') != 'saveOrderClients') {
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            
            $sortedIds = $request->getPost('sorted_ids_clients');
            
            if(empty($sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
            }
            
            $sortedIds = trim($sortedIds, ' ,');
            
            if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }
            
            $sortedIds = explode(',', $sortedIds);
            
            $cmsClientsTable = new Application_Model_DbTable_CmsClients();
            
            $cmsClientsTable->updateOrderOfClients($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
        
        }
        
    }
}


