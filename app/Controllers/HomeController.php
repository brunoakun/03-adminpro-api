<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Router\RouteCollection;

class HomeController extends ResourceController
{
    public function index()
    {
        $response = [
            'status' => 500,
            "error" => true,
            'messages' => 'Para la lista de los endPoints de la API ',
            'data' => ['https://sprint09.cerolab.com/myLista']
        ];
        return $this->respond($response);
    }

    public function myLista()
    {
        // Get the RouteCollection instance
        $routes = service('routes');

        // Get all registered routes
        $allRoutes = $routes->getRoutes();

        // Loop through all routes and display them
        $routeString = '';

        // Loop through all routes and concatenate them into a string
        foreach ($allRoutes as $route) {
            $routeString .= "$route" . "<br>";
        }

        return $this->respond($routeString);
    }
}
