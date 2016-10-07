<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction() {


		$request = $this->getRequest();
		$flashMessenger = $this->getHelper('FlashMessenger');

		$form = new Application_Form_Frontend_Contact();

		$systemMessages = 'init';
		
		if ($request->isPost() && $request->getPost('task') === 'contact') {

			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) {
					throw new Application_Model_Exception_InvalidInput('Invalid form data bla bla');
				}

				//get form data
				$formData = $form->getValues();

				// do actual task
				//save to database etc
				$mailHelper = new Application_Model_Library_MailHelper();
				$from_email = $formData['email'];
				$to_email = 'alexsandarrr@gmail.com';
				$from_name = $formData['name'];
				$message = $formData['message'];
				
				$result = $mailHelper->sendmail($to_email, $from_email, $from_name, $message);
				
				if(!$result) {
					$systemMessages = "Error";
				} else {
					$systemMessages = "Success";
				}
				
				//redirect to same or another page
				
			} catch (Application_Model_Exception_InvalidInput $ex) {
				$systemMessages['errors'][] = $ex->getMessage();
			}
		}

		$this->view->systemMessages = $systemMessages;
		$this->view->form = $form;
	}

	public function askmemberAction () {
        
        $request = $this->getRequest();
        
        $id = (int) $request->get('id');
        
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
        $member = $cmsMembersDbTable->getMemberById($id);
        
        $this->view->member = $member;
    }


}

