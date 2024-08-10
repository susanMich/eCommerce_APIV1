<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $data = [
            "mensaje" => "Bienvenidos a la API de Plataforma eCommerce ",
            "fecha" => date('Y-m-d H:i:s')
        ];
        return json_encode($data);
    }
}
