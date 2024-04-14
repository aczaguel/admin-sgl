<?php

namespace App\Models;

use GroceryCrud\Core\Model;

class TraDocStatusModel extends Model
{
    protected $table = 'tra_doc_status';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'folio_tramite',
        'tramite_id',
        'documento_id',
        'status_documento_id',
        'file',
        'comentario',
        'created_at',
        'updated_at',
        'user_id',
        'status'
    ];

    // Opcional: Puedes definir validaciones aquí si lo deseas
    // protected $validationRules = [
    //     'tra_tipos_id' => 'required',
    //     'documento_id' => 'required',
    //     'created_at' => 'required',
    //     'updated_at' => 'required',
    //     'status' => 'required'
    // ];

    // Opcional: Puedes definir mensajes de error personalizados aquí si lo deseas
    // protected $validationMessages = [
    //     'tra_tipos_id' => [
    //         'required' => 'El campo tra_tipos_id es obligatorio.'
    //     ],
    //     'documento_id' => [
    //         'required' => 'El campo documento_id es obligatorio.'
    //     ],
    //     // Y así sucesivamente para cada campo y regla de validación
    // ];
}
