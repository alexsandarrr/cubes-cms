<?php

class Admin_UsersController extends Zend_Controller_Action
{
    public function indexAction () {
        
        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();
        
        $loggerdInUser = Zend_Auth::getInstance()->getIdentity();
        
        $users = $cmsUsersDbTable->search(array(
            'filters' => array(
                'id_exclude' => $loggerdInUser['id'],
            ),
            'orders' => array(
                'first_name' => 'ASC',
            ),
//            'limit' => 3,
//            'page' => 2,
        ));
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        
        $this->view->users = $users;
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

        if ($request->isPost() && $request->getPost('task') === 'deleteUser') {

            try {
                
                $cmsUsersTable->deleteUser($id);
                
                $flashMessenger->addMessage('User has been deleted', 'success');

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

        if ($request->isPost() && $request->getPost('task') === 'disableUser') {

            try {
                
                $cmsUsersTable->disableUser($id);
                
                $flashMessenger->addMessage('User has been disabled', 'success');

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

        if ($request->isPost() && $request->getPost('task') === 'enableUser') {

            try {
                
                $cmsUsersTable->enableUser($id);
                
                $flashMessenger->addMessage('User has been enabled', 'success');

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
                
                $flashMessenger->addMessage('Password has been restet', 'success');

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
    }
}

