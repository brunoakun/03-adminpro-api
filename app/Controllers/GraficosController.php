<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class GraficosController extends ResourceController
{
    public function dataDonut()
    {
        $response = [
            'status' => 200,
            "error" => false,
            'messages' => 'Datos fake para gráficos tipo Donut',
            'data' => [
                'labels' => ['Vendedor A', 'Vendedor B', 'Ventas WEB'],
                'dataset1' => [350, 450, 215],
                "dataset2" => [50, 150, 120],
                "dataset3" => [250, 130, 70]
            ]
        ];
        return $this->respond($response);
    }
}
