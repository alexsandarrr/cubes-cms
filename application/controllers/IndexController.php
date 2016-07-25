<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
        
        // $select je objekat klase Zend_Db_Select
        $select = $cmsClientsDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsClients::STATUS_ENABLED)
                ->order('order_number');
        
        // debug za db select - vraca se sql upit
        //die($select->assemble());
        
        $clients = $cmsClientsDbTable->fetchAll($select);
        
        
        $this->view->clients = $clients;
    }

    public function testAction()
    {
        
    }
    
}

