<?php

namespace App\Models;

use GroceryCrud\Core\Model;

class BitacoraModel extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'folio_tramite',
        'tramite_id',
        'cambios',
        'created_at',
        'updated_at',
        'user_id',
        'status'
    ];
    // public function __construct() {
    //     parent::__construct();
    // }

    // public function get_list() {
    //     $this->db->select('bitacora.*, users.username as user_name');
    //     $this->db->from('bitacora');
    //     $this->db->join('users', 'bitacora.user_id = users.id', 'left');
    //     return $this->db->get()->result();
    // }

    // public function get_list_count() {
    //     $this->db->from('bitacora');
    //     return $this->db->count_all_results();
    // }

    // public function insert($post_array) {
    //     $post_array['created_at'] = date('Y-m-d H:i:s');
    //     $post_array['updated_at'] = date('Y-m-d H:i:s');
    //     return parent::insert($post_array);
    // }

    // public function update($primary_key, $post_array) {
    //     $post_array['updated_at'] = date('Y-m-d H:i:s');
    //     return parent::update($primary_key, $post_array);
    // }

    
}
