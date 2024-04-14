<?php
//DocumentsModel.php

use GroceryCrud\Core\Model;
use GroceryCrud\Core\Model\ModelFieldType;

class DocumentsModel extends Model {

    protected $ci;
    protected $db;

    function __construct($databaseConfig) {
        $this->setDatabaseConnection($databaseConfig);

        $this->ci = & get_instance();
        $this->db = $this->ci->db;
    }

    public function getFieldTypes($tableName)
    {
        $fieldTypes = parent::getFieldTypes($tableName);

        $fullNameFieldType = new ModelFieldType();
        $fullNameFieldType->dataType = 'varchar';

        $countOrdersFieldType = new ModelFieldType();
        $countOrdersFieldType->dataType = 'varchar';

        $fieldTypes['fullname'] = $fullNameFieldType;
        $fieldTypes['count_orders'] = $countOrdersFieldType;

        return $fieldTypes;
    }

    public function getOne($id)
    {
        $customer = parent::getOne($id);

        $this->db->select('COUNT(*) as count_orders');
        $this->db->where('customerNumber', $id);
        $customer['count_orders'] = $this->db->get('orders')->row()->count_orders;

        return $customer;
    }

    protected function _getQueryModelObject($withLimit = true) {
        $order_by = $this->orderBy;
        $sorting = $this->sorting;

        // All the custom stuff here
        $this->db->select('customers.customerNumber, CONCAT(customerName, \' \' ,contactLastName) as fullname, phone, city, country, COUNT(*) as count_orders', false);
        $this->db->join('orders', 'orders.customerNumber = customers.customerNumber', 'left');
        $this->db->group_by('customers.customerNumber');

        if ($order_by !== null) {
            $this->db->order_by($order_by. " " . $sorting);
        }

        if (!empty($this->_filters)) {
            foreach ($this->_filters as $filter_name => $filter_value) {
                if ($filter_name !== 'fullname') {
                    if (is_array($filter_value)) {
                        foreach ($filter_value as $value) {
                            $this->db->like($filter_name, $value);    
                        }
                    } else {
                        $this->db->like($filter_name, $filter_value);
                    }                    
                } else {
                    if (is_array($filter_value)) {
                        foreach ($filter_value as $value) {
                            $this->db->like('CONCAT(customerName, \' \' ,contactLastName)', $value);
                        }
                    } else {
                        $this->db->like('CONCAT(customerName, \' \' ,contactLastName)', $filter_value);
                    }   
                }
            }
        }

        if (!empty($this->_filters_or)) {
            foreach ($this->_filters_or as $filter_name => $filter_value) {
                $this->db->or_like($filter_name, $filter_value);
            }
        }

        if ($withLimit) {
            $this->db->limit($this->limit, ($this->limit * ($this->page - 1)));
        }

        return $this->db->get($this->tableName);
    }

    public function getList() {
        return $this->_getQueryModelObject()->result_array();
    }

    public function getTotalItems()
    {
        if (!empty($this->_filters)) {
            return $this->_getQueryModelObject(false)->num_rows();
        }

        // If we don't have any filtering it is faster to have the default total items
        // In case this is more complicated you can add your own code here
        return parent::getTotalItems();
    }
}