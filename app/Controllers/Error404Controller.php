<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Error404Controller extends ResourceController
{
    public function corsAllow()
    {
        // Este controlador en concreto se ejecuta antes de los filtros, por eso es necesario añadir las cabeceras manualmente 
        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    public function index()
    {
        $this->corsAllow();
        $response = [
            'status' => 200,
            "error" => true,
            'mensaje' => 'Error 400. URL no encontrada. Para la lista de los endPoints de la API consulta https://sprint09.cerolab.com/myLista ',
            'data' => ['https://sprint09.cerolab.com/myLista']
        ];
        return $this->respond($response);
    }
}
