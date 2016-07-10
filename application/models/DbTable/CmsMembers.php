<?php

class Application_Model_DbTable_CmsMembers extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_members';
    
    /** 
     * @param int $id
     * @return null|array Associative array with keys as cms_members table columns or NULL if not found
     */
    public function getMemberById ($id) {
        
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
     * @param array $member Associative array with keys as column names and values as column new values
     */
    public function updateMember ($id, $member) {
        
        if (isset($member['id'])) {
            // Forbid changing of user id
            unset($member['id']);
        }
        
        $this->update($member, 'id = ' . $id);
    }
    
    /**
     * @param array $member Associative array with keys as column names and values as column new values
     * @return int The ID of new member (autoincrement)
     */
    public function insertMember ($member) {
        // fetch order number for new member
        
        $id = $this->insert($member);
        
        return $id;
    }
}

