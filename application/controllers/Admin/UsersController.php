<?php

class Admin_UsersController extends Zend_Controller_Action
{
    public function indexAction () {
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        
        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();
        
        $users = $cmsUsersDbTable->search(array(
            //'filters' => array(),
            'orders' => array(
                'order_number' => 'ASC'
            ),
            //'limit' => 4,
            //'page' => 3
        ));
        
        $this->view->users = array();
        $this->view->systemMessages = $systemMessages;
    }
    
    public function addAction () {
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_UserAdd();

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
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new user');
                }

                //get form data
                $formData = $form->getValues();
                
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
                $userId = $cmsUsersTable->insertUser($formData);
                

                // do actual task
                //save to database etc
                
                
                
                //set system message
                $flashMessenger->addMessage('User has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
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
            throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
        }
        
        $loggedinUser = Zend_Auth::getInstance()->getIdentity();
        
        if ($id == $loggedinUser['id']) {
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
                
        }
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
        $user = $cmsUsersTable->getUserById($id);
        
        if (empty($user)) {
            throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_UserEdit($user['id']);

        //default form data
        $form->populate($user);

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'update') {

            try {

                // check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for user');
                }

                // get form data
                $formData = $form->getValues();
                
                // radimo update postojeceg zapisa u tabeli
                $cmsUsersTable->updateUser($user['id'], $formData);

                // do actual task
                // save to database etc
                
                // set system message
                $flashMessenger->addMessage('User has been updated', 'success');

                // redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        
        $this->view->user = $user;
    }
    
    public function deleteAction () {
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
        }
        
        $loggedinUser = Zend_Auth::getInstance()->getIdentity();
        
        if ($id == $loggedinUser['id']) {
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
                
        }
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
        $user = $cmsUsersTable->getUserById($id);
        
        if (empty($user)) {
            throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'delete') {

            try {
                
                $cmsUsersTable->deleteUser($id);
                
                 $request instanceof Zend_Controller_Request_Http;
                
                if ($request->isXmlHttpRequest()) {
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted'
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                
                    $flashMessenger->addMessage('User has been deleted', 'success');

                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                    ), 'default', true);
                }
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                if ($request->isXmlHttpRequest()) {
                    //request is ajax
                    
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                    
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                    ), 'default', true);
                }
                
            }
        }

        $this->view->systemMessages = $systemMessages;
    }
    
    public function disableAction () {
        
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
        }
        
        $loggedinUser = Zend_Auth::getInstance()->getIdentity();
        
        if ($id == $loggedinUser['id']) {
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
                
        }
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
        $user = $cmsUsersTable->getUserById($id);
        
        if (empty($user)) {
            throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'disable') {

            try {
                
                $cmsUsersTable->disableUser($id);
                
                $request instanceof Zend_Controller_Request_Http;
                
                if ($request->isXmlHttpRequest()) {
                    //request is ajax request
                    // send response as json
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been disabled'
                    );
                    
                    // send json as response
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                    // request is not ajax
                    // send message over session (flashMessanger)
                    // and do redirect
                    
                    $flashMessenger->addMessage('User has been disabled', 'success');

                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                    ), 'default', true);
                }
            } catch (Application_Model_Exception_InvalidInput $ex) {
                if ($request->isXmlHttpRequest()) {
                    //request is ajax
                    
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                    
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');

                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                    ), 'default', true);
                }
            }
        }

        $this->view->systemMessages = $systemMessages;
        
    }

    public function enableAction () {
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
        }
        
        $loggedinUser = Zend_Auth::getInstance()->getIdentity();
        
        if ($id == $loggedinUser['id']) {
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
                
        }
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
        $user = $cmsUsersTable->getUserById($id);
        
        if (empty($user)) {
            throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'enable') {

            try {
                
                $cmsUsersTable->enableUser($id);
                
                $request instanceof Zend_Controller_Request_Http;
                
                if ($request->isXmlHttpRequest()) {
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been enabled'
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                
                    $flashMessenger->addMessage('User has been enabled', 'success');

                    // redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                    ), 'default', true);
                }
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                if ($request->isXmlHttpRequest()) {
                    //request is ajax
                    
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                    
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                    ), 'default', true);
                }
            }
        }

        $this->view->systemMessages = $systemMessages;
    }
    
    public function resetpasswordAction () {
        $request = $this->getRequest();
        
        $id = (int) $request->getParam('id');
        
        if ($id <= 0) {
            
            // prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
        }
        
        $loggedinUser = Zend_Auth::getInstance()->getIdentity();
        
        if ($id == $loggedinUser['id']) {
            
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
                
        }
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
        $user = $cmsUsersTable->getUserById($id);
        
        if (empty($user)) {
            throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
        }
        
        
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

        if ($request->isPost() && $request->getPost('task') === 'resetPassword') {

            try {
                
                $cmsUsersTable->changeUserPassword($id, Application_Model_DbTable_CmsUsers::DEFAULT_PASSWORD);
                
                $request instanceof Zend_Controller_Request_Http;
                
                if ($request->isXmlHttpRequest()) {
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => 'Password for user ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been reset'
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                
                $flashMessenger->addMessage('Password has been reset', 'success');

                // redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
                }
                
            } catch (Application_Model_Exception_InvalidInput $ex) {
                
                if ($request->isXmlHttpRequest()) {
                    //request is ajax
                    
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                } else {
                    
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                    
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                    ), 'default', true);
                }
            }
        }

        $this->view->systemMessages = $systemMessages;
    }
    
    public function datatableAction() {
        
        $request = $this->getRequest();
        
        $datatableParameters = $request->getParams();
        
    /*
    $datatableParameters ce biti u ovom formatu
        Array
            (
                [controller] => admin_users
                [action] => datatable
                [module] => default
    
                [draw] => 2
    
                [order] => Array
                    (
                        [0] => Array
                            (
                                [column] => 2
                                [dir] => asc
                            )

                    )

                [start] => 0
                [length] => 5
                [search] => Array
                    (
                        [value] => 
                        [regex] => false
                    )
        ) 

    */
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
        
        $loggedInUser = Zend_Auth::getInstance()->getIdentity();
        $filters = array(
            'id_exclude' => $loggedInUser['id']
        );
        $orders = array();
        $limit = 5;
        $page = 1;
        $draw = 1;
        
        $columns = array('status', 'username', 'first_name', 'last_name', 'email', 'actions');
        
        // Process datatable parameters
        
        if (isset($datatableParameters['draw'])) {
            
            $draw = $datatableParameters['draw'];
            
            if (isset($datatableParameters['length'])) {
                
                // limit rows per page
                $limit = $datatableParameters['length'];
                
                if (isset($datatableParameters['start'])) {
                    
                    $page = floor($datatableParameters['start'] / $datatableParameters['length']) + 1;
                }
            }
            
            if (
                isset($datatableParameters['order']) && 
                is_array($datatableParameters['order'])
                ) {
                
                foreach ($datatableParameters['order'] as $datatableOrder) {
                    $columnIndex = $datatableOrder['column'];
                    $orderDirection = strtoupper($datatableOrder['dir']);
                    
                    if (isset($columns[$columnIndex])) {
                        
                    }
                    
                    $orders[$columns[$columnIndex]] = $orderDirection;
                }
            }
            
            if (
                isset($datatableParameters['search']) &&
                is_array($datatableParameters['search']) &&
                isset($datatableParameters['search']['value'])
                ) {
                
                $filters['username_search'] = $datatableParameters['search']['value'];
                
            }
        }
        
        $users = $cmsUsersTable->search(array(
            'filters' => $filters,
            'orders' => $orders,
            'limit' => $limit,
            'page' => $page
        ));
        
        $usersFilteredCount = $cmsUsersTable->count($filters);
        $usersTotal = $cmsUsersTable->count();
        
        $this->view->users = $users;
        $this->view->usersFilteredCount = $usersFilteredCount;
        $this->view->usersTotal = $usersTotal;
        $this->view->draw = $draw;
        $this->view->columns = $columns;
        
    }
    
    public function dashboardAction () {
     
        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();
        
        $usersTotal = $cmsUsersDbTable->count(array(
            'orders' => array(
                'order_number' => 'ASC'
                )
        ));
        
        $usersActive = $cmsUsersDbTable->count(array(
            'status' => Application_Model_DbTable_CmsUsers::STATUS_ENABLED
        ));
        
        $this->view->usersTotal = $usersTotal;
        $this->view->usersActive = $usersActive;
    }
}

