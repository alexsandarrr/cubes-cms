<?php

class Admin_SessionController extends Zend_Controller_Action
{
    public function indexAction () {
        
        // provera da li je korisnik ulogovan
        if (Zend_Auth::getInstance()->hasIdentity()) {
            //ulogovan je
            
            // redirect na admin_dashboard kontroler i index akciju
            
            $redirector = $this->getHelper('Redirector');
            $redirector instanceof Zend_Controller_Action_Helper_Redirector;

            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_dashboard',
                        'action' => 'index',

                    ), 'default', true);
            
        } else {
            
            // nije ulogovan
            
            // redirect na login stranu
            $redirector = $this->getHelper('Redirector');
            $redirector instanceof Zend_Controller_Action_Helper_Redirector;

            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_session',
                        'action' => 'login',

                    ), 'default', true);
        }
        
        
    }
    
    public function loginAction () {
        
        // Disable-ovanje layout-a
        Zend_Layout::getMvcInstance()->disableLayout();
        
        $loginForm = new Application_Form_Admin_Login();
        
        $request = $this->getRequest();
        $request instanceof Zend_Controller_Request_Http;
        
        //$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger'); - moze i ovako da se dohvata helper za flash messenger, ovo je van kontrolera, a ispod je kada smo u kontroleru
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        if ($request->isPost() && $request->getPost('task') === 'login') {
            
            if ($loginForm->isValid($request->getPost())) {
                
                $authAdapter = new Zend_Auth_Adapter_DbTable();
                $authAdapter->setTableName('cms_users')
                        ->setIdentityColumn('username')
                        ->setCredentialColumn('password')
                        ->setCredentialTreatment('MD5(?) AND status != 0');
                
                $authAdapter->setIdentity($loginForm->getValue('username'));
                $authAdapter->setCredential($loginForm->getValue('password'));
                
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);
                
                if ($result->isValid()) {
                    
                    // Smestanje kompletnog reda iz tabele cms_users kao identifikator da je korisnik ulogovan
                    // Po defaultu se smesta samo username, a ovako smestamo asocijativni niz, tj. row iz tabele
                    // Asocijativni niz $user ima kljuceve koji su u stvari nazivi kolona u tabeli cms_users
                    $user = $authAdapter->getResultRowObject();
                    $auth->getStorage()->write($user);
                    
                    $redirector = $this->getHelper('Redirector');
                    $redirector instanceof Zend_Controller_Action_Helper_Redirector;

                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_dashboard',
                                'action' => 'index',
                                    ), 'default', true);
                    
                } else {
                    $systemMessages['errors'][] = 'Wrong username or password';
                }
                
            } else {
                $systemMessages['errors'][] = 'Username and password are required';
            }
        }
        
        $this->view->systemMessages = $systemMessages;
    }
    
    public function logoutAction () {
        $auth = Zend_Auth::getInstance();
        
        // Brise indikator da je neko ulogovan
        $auth->clearIdentity();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        $flashMessenger->addMessage('You have been logged out', 'success');
        
        // Ovde ide redirect na login stranu
        
        $redirector = $this->getHelper('Redirector');
        $redirector instanceof Zend_Controller_Action_Helper_Redirector;
        
        $redirector->setExit(true)
                ->gotoRoute(array(
                    'controller' => 'admin_session',
                    'action' => 'login',
                    
                ), 'default', true);
        /*
        $redirector->setExit(true)
                ->gotoSimp('login', 'admin_session'); // Prosledjujemo ono sto je u url-u za controller i action
        
        $redirector->setExit(true)
                ->gotoUrl('/admin_session/login'); // Prosledjujemo url na koji zelimo da preusmerimo nekoga, ili za preusmeravanje na spoljni link
        
        // Redirect na spoljni link
        $redirector->setExit(true)
                ->setPrependBase(false)
                ->goroUrl('https://www.facebook.com');
        */
    }
}

