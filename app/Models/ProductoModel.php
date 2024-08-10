<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ProductoModel extends Model
{

    protected $table      = 'producto';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'idcategoria', 
        'idmarca', 
        'descripcion', 
        'precio', 
        'pvp', 
        'impuesto', 
        'foto'
    ];

    // Optionally define validation rules
    protected $validationRules = [
        'descripcion' => 'required|min_length[3]|max_length[255]',
        'precio' => 'required|decimal',
        'pvp' => 'required|decimal',
        'impuesto' => 'required|decimal',
    ];
    
   /* protected $beforeInsert = ['beforeInsert'];
    protected $beforeUpdate = ['beforeUpdate'];
    

    protected function beforeInsert(array $data): array
    {
       /* $data['data']['fechacreacion'] = date("Y-m-d H:i:s"); 
        $data['data']['fechamodificacion'] = null; 
        $data['data']['usrmodifico'] = null;
        $data['data']['usrcambiaestado'] = null;
        $data['data']['top'] = 0; 
        $data['data']['estado'] = 1; //Activo
        return $data;
    }

    protected function beforeUpdate(array $data): array
    {
        $data['data']['fechamodificacion'] = date("Y-m-d H:i:s"); 
        return $data;
    }*/


}