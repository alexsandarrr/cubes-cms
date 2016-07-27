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
        
        $select = $this->select();
        
        // sort rows by order_number DESCENDING and fetch one row from the top
        // with biggest order_number
        $select->order('order_number DESC');
        
        $memberWithBiggerstOrderNumber = $this->fetchRow($select);
        
        if ($memberWithBiggerstOrderNumber instanceof Zend_Db_table_Row) {
            
            $member['order_number'] = $memberWithBiggerstOrderNumber['order_number'] + 1;
            
        } else {
            // table was empty, we are inserting first member
            $member['order_number'] = 1;
        }
        
        $id = $this->insert($member);
        
        return $id;
    }
    
    /**
     * @param int $id ID of member to delete
     */
    public function deleteMember ($id) {
        
        $memberPhotoFilePath = PUBLIC_PATH . '/uploads/members/' . $id . '.jpg';
        if(is_file($memberPhotoFilePath)) {
            //delete member photo file
            unlink($memberPhotoFilePath);
        }
        
        // member who is going to be deleted
        $member = $this->getMemberById($id);
        
        $this->update(array(
           'order_number' => new Zend_Db_Expr('order_number - 1') 
        ),
        'order_number > ' . $member['order_number']);
        
        $this->delete('id = ' . $id);
    }
    
    /**
     * @param int $id ID of member to disable
     */
    public function disableMember ($id) {
        
        $this->update(array(
            'status' => self::STATUS_DISABLED
        ), 'id = ' . $id);
    }
    
    /**
     * @param int $id ID of member to enable
     */
    public function enableMember ($id) {
        
        $this->update(array(
            'status' => self::STATUS_ENABLED
        ), 'id = ' . $id);
    }
    
    public function updateOrderOfMembers ($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            
            $this->update(array(
            'order_number' => $orderNumber + 1 // +1 because order_number starts from 1, not from 0
        ), 'id = ' . $id);
            
        }
    }
}

