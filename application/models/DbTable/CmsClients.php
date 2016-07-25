<?php

class Application_Model_DbTable_CmsClients extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_clients';
    
    /** 
     * @param int $id
     * @return null|array Associative array with keys as cms_clients table columns or NULL if not found
     */
    public function getClientById ($id) {
        
        $select = $this->select();
        $select->where('id = ?', $id);
        
        $row = $this->fetchRow($select);
        
        if ($row instanceof Zend_Db_Table_Row) {
            
            return $row->toArray();
        } else {
            // row is not found
            return null;
        }
    }
    
    /**
     * 
     * @param array $client Associative array with keys as column names and values as column new values
     * @return int ID of new client
     */
    public function insertClient ($client) {
        
        return $this->insert($client);
        
    }
    
    /**
     * @param int $id ID of client to delete
     */
    public function deleteClient ($id) {
        
        // client who is going to be deleted
        $client = $this->getClientById($id);
        
        $this->delete('id = ' . $id);
    }

    /**
     * @param int $id ID of client to disable
     */
    public function disableClient ($id) {
        
        $this->update(array(
            'status' => self::STATUS_DISABLED
        ), 'id = ' . $id);
    }
    
    /**
     * @param int $id ID of client to enable
     */
    public function enableClient ($id) {
        
        $this->update(array(
            'status' => self::STATUS_ENABLED
        ), 'id = ' . $id);
    }
    /**
     * @param int $id
     * @param array $client Associative array with keys as column names and values as column new values
     */
    public function updateClient ($id, $client) {
        
        if (isset($client['id'])) {
            // Forbid changing of client id
            unset($client['id']);
        }
        
        $this->update($client, 'id = ' . $id);
    }
    
    public function updateOrderOfClients ($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            
            $this->update(array(
            'order_number' => $orderNumber + 1
        ), 'id = ' . $id);
            
        }
    }
}

