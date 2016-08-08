<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    
    public function askmemberAction () {
        
        $request = $this->getRequest();
        
        $id = (int) $request->get('id');
        
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
        $member = $cmsMembersDbTable->getMemberById($id);
        
        $this->view->member = $member;
    }


}

