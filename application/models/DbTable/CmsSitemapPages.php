<?php

class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_sitemap_pages';
    
    protected static $sitemapPagesMap;
    
    /**
     * 
     * @return array With keys as sitemap page ids and values as assoc. array with keys url and type
     */
    public static function getSitemapPagesMap() {
        // lazy loading
        
        if (!self::$sitemapPagesMap) {
            
            $sitemapPagesMap = array ();
        
            $cmsSitemapPagesDbTable = new self();
            // same as
            // $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages()

            $sitemapPages = $cmsSitemapPagesDbTable->search(array(
               'orders' => array (
                   'parent_id' => 'ASC',
                   'order_number' => 'ASC'
               ) 
            ));

            foreach ($sitemapPages as $sitemapPage) {

                $type = $sitemapPage['type'];
                $url = $sitemapPage['url_slug'];

                if (isset($sitemapPagesMap[$sitemapPage['parent_id']])) {
                    $url = $sitemapPagesMap[$sitemapPage['parent_id']]['url'] . '/' . $url;
                }

                $sitemapPagesMap[$sitemapPage['id']] = array (
                    'url' => $url,
                    'type' => $type
                );
            }

            return self::$sitemapPagesMap = $sitemapPagesMap;
        }
        
        return self::$sitemapPagesMap;
    }
    
    public function getSitemapPageById ($id) {
        
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
     * @param array $sitemapPage Associative array with keys as column names and values as column new values
     */
    public function updateSitemapPage ($id, $sitemapPage) {
        
        if (isset($sitemapPage['id'])) {
            // Forbid changing of user id
            unset($sitemapPage['id']);
        }
        
        $this->update($sitemapPage, 'id = ' . $id);
    }
    
    /**
     * @param array $sitemapPage Associative array with keys as column names and values as column new values
     * @return int The ID of new sitemapPage (autoincrement)
     */
    public function insertSitemapPage ($sitemapPage) {
        // fetch order number for new sitemapPage
        
        $select = $this->select();
        
        // sort rows by order_number DESCENDING and fetch one row from the top
        // with biggest order_number
        $select->where('parent_id = ?', $sitemapPage['parent_id'])
                ->order('order_number DESC');
        
        $sitemapPageWithBiggerstOrderNumber = $this->fetchRow($select);
        
        if ($sitemapPageWithBiggerstOrderNumber instanceof Zend_Db_table_Row) {
            
            $sitemapPage['order_number'] = $sitemapPageWithBiggerstOrderNumber['order_number'] + 1;
            
        } else {
            // table was empty, we are inserting first sitemapPage
            $sitemapPage['order_number'] = 1;
        }
        
        $id = $this->insert($sitemapPage);
        
        return $id;
    }
    
    /**
     * @param int $id ID of sitemapPage to delete
     */
    public function deleteSitemapPage ($id) {
        
        // sitemapPage who is going to be deleted
        $sitemapPage = $this->getSitemapPageById($id);
        
        $sitemapPageChildren = $this->search(array(
           'filters' => array(
               'parent_id' => $sitemapPage['id']
           ) 
        ));
        
        // delete children pages recursively
        foreach ($sitemapPageChildren as $sitemapPageChild) {
            $this->deleteSitemapPage($sitemapPageChild['id']);
        }
        
        $this->update(array(
           'order_number' => new Zend_Db_Expr('order_number - 1') 
        ),
        'order_number > ' . $sitemapPage['order_number'] . ' AND parent_id = ' . $sitemapPage['parent_id']);
        
        $this->delete('id = ' . $id);
    }
    
    /**
     * @param int $id ID of sitemapPage to disable
     */
    public function disableSitemapPage ($id) {
        
        $this->update(array(
            'status' => self::STATUS_DISABLED
        ), 'id = ' . $id);
    }
    
    /**
     * @param int $id ID of sitemapPage to enable
     */
    public function enableSitemapPage ($id) {
        
        $this->update(array(
            'status' => self::STATUS_ENABLED
        ), 'id = ' . $id);
    }
    
    public function updateOrderOfSitemapPages ($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            
            $this->update(array(
            'order_number' => $orderNumber + 1 // +1 because order_number starts from 1, not from 0
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
                    case 'short_title':
                    case 'url_slug':
                    case 'title':
                    case 'parent_id':
                    case 'type':
                    case 'order_number':
                    case 'status':
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
                case 'short_title':
                case 'url_slug':
                case 'title':
                case 'parent_id':
                case 'type':
                case 'order_number':
                case 'status':
                    if (is_array($value)) {
                        $select->where($field . ' IN (?)', $value);
                    } else {
                        $select->where($field . ' = ?', $value);
                    }
                    break;

                case 'short_title_search':
                    $select->where('short_title_search LIKE ?', '%' . $value . '%');
                    break;

                case 'title_search':
                    $select->where('title_search LIKE ?', '%' . $value . '%');
                    break;

                case 'description_search':
                    $select->where('description_search LIKE ?', '%' . $value . '%');
                    break;

                case 'body_search':
                    $select->where('body_search LIKE ?', '%' . $value . '%');
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
    
    /**
     * 
     * @param int $id The ID of sitemap page
     * @return array Sitemap page rows in path
     */
    public function getSitemapPageBreadcrumbs($id) {
        
        $sitemapPageBreadcrumbs = array();
        
        while($id > 0) {
            
            $sitemapPageInPath = $this->getSitemapPageById($id);
            
            if ($sitemapPageInPath) {
                
                $id = $sitemapPageInPath['parent_id'];
                
                // add current page at the beggining of breadcrumbs array
                array_unshift($sitemapPageBreadcrumbs, $sitemapPageInPath);
                
            } else {
                
                $id = 0;
                
            }
            
        }
        
        return $sitemapPageBreadcrumbs;
    }
    
    /**
     * Returns count by type example:
     * array (
     *      'StaticPage' => 3,
     *      'AboutUsPage' => 1,
     *      'ContactPage' => 1,
     *      ...
     * @param type $filters
     * @return array Count by type
     */
    public function countByTypes ($filters = array ()) {
        
        $select = $this->select();
        
        $this->processFilters($filters, $select);
        
        // reset previously set columns for resultset
        $select->reset('columns');
        // set one column/field to fetch and it is COUNT function
        $select->from($this->_name, array(
            'type',
            'COUNT(*) as total_by_type'
        ));
        $select->group('type');
        
        $rows = $this->fetchAll($select);
        
        $countByTypes = array ();
        
        foreach ($rows as $row) {
            $countByTypes[$row['type']] = $row['total_by_type'];
        }
        
        return $countByTypes;
    }
}

