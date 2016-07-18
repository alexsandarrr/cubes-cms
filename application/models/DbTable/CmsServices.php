<?php

class Application_Model_DbTable_CmsServices extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_services';
    
    /** 
     * @param int $id
     * @return null|array Associative array with keys as cms_services table columns or NULL if not found
     */
    public function getServiceById ($id) {
        
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
     * @param int $id
     * @param array $service Associative array with keys as column names and values as column new values
     */
    public function updateService ($id, $service) {
        
        if (isset($service['id'])) {
            
            unset($service['id']);
        }
        
        $this->update($service, 'id = ' . $id);
    }
    
    /**
     * @param array $service Associative array with keys as column names and values as column new values
     * @return int The ID of new service (autoincrement)
     */
    public function insertService ($service) {
        
        $id = $this->insert($service);
        
        return $id;
    }
    
    /**
     * @param int $id ID of service to delete
     */
    public function deleteService($id) {
        
        $this->delete('id = ' . $id);
    }
    
    /**
     * @param int $id ID of service to disable
     */
    public function disableService ($id) {
        
        $this->update(array(
            'status' => self::STATUS_DISABLED
        ), 'id = ' . $id);
    }
    
    /**
     * @param int $id ID of service to enable
     */
    public function enableService ($id) {
        
        $this->update(array(
            'status' => self::STATUS_ENABLED
        ), 'id = ' . $id);
    }
    
    public function updateOrderOfServices ($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            
            $this->update(array(
            'order_number' => $orderNumber + 1
        ), 'id = ' . $id);
            
        }
    }
}

