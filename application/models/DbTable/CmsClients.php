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
    
        /**
     * Array $parameters is keeping search parameters.
     * Array $parameters must be in following format:
     *      array(
     *          'filters' => array(
     *              'status' => 1,
     *              'id' => array(3, 8, 11),
     *          ),
     *          'orders' =>array(
     *              'username' => 'ASC', //key is column, if value is ASC then ORDER BY ASC
     *              'first_name' => 'DESC', // key is column, if value is DESC then ORDER BY DESCS
     *          ),
     *          'limit' => 50, // limit result set to 50 rows
     *          'page' => 3 // start from page 3. If no limit is set, page is ignored
     *      )
     * @param array $parameters Asoc array with keys "filters", "orders", "limit" and "page".
     */
    public function search (array $parameters = array()) {
        
        $select = $this->select();
        
        if (isset($parameters['filters'])) {
            
            $filters = $parameters['filters'];
            
            $this->processFilters($filters, $select);
            
        }
        
        if (isset($parameters['orders'])) {
            
            $orders = $parameters['orders'];
            
            foreach ($orders as $field => $orderDirection) {
                
                switch ($field) {
                    
                    case 'id':
                    case 'name':
                    case 'description':
                    case 'status':
                    case 'order_number':
                        if ($orderDirection === 'DESC') {
                            $select->order($field . ' DESC');
                        } else {
                            $select->order($field);
                        }
                        break;
                }
            }
        }
        
        if (isset($parameters['limit'])) {
            
            if (isset($parameters['page'])) {
                // page is set do limit by page
                $select->limitPage($parameters['page'], $parameters['limit']);
            } else {
                // page is not set, just do regular limit
                $select->limit($parameters['limit']);
            }
        }
        
        //die ($select->assemble());
        
        return $this->fetchAll($select)->toArray();
    }
    
    /**
     * @param array $filters See function search $parameters['filters']
     * @return int Count of rows that match $filters
     */
    public function count(array $filters = array()) {
        
        $select = $this->select();
        
        $this->processFilters($filters, $select);
        
        // reset previously set columns for resultset
        $select->reset('columns');
        // set one column/field to fetch and it is COUNT function
        $select->from($this->_name, 'COUNT(*) as total');
        
        $row = $this->fetchRow($select);
        
        return $row['total'];
    }
    
    /**
     * Fill $select object with WHERE conditions
     * @param array $filters
     * @param Zend_Db_Select $select
     */
    protected function processFilters(array $filters, Zend_Db_Select $select) {
        
        // $select ovject will be modified outside this function
        // obect are always passed by reference
        foreach ($filters as $field => $value) {
                
            if ($field == 'id') {

                if (is_array($value)) {
                    $select->where('id IN (?)', $value);
                } else {
                    $select->where('id = ?', $value);
                }
            }

            switch ($field) {

                case 'id':
                case 'name':
                case 'description':
                case 'status':
                    if (is_array($value)) {
                        $select->where($field . ' IN (?)', $value);
                    } else {
                        $select->where($field . ' = ?', $value);
                    }
                    break;

                case 'name_search':
                    $select->where('name LIKE ?', '%' . $value . '%');
                    break;

                case 'description_search':
                    $select->where('description LIKE ?', '%' . $value . '%');
                    break;

                case 'id_exclude':
                    if (is_array($value)) {
                        $select->where('id NOT IN (?)', $value);
                    } else {
                        $select->where('id != ?', $value);
                    }
                    break;
            }
        }
    }
}

