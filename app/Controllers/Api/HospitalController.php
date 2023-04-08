<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\HospitalModel;

class HospitalController extends ResourceController
{

    /**
     * hospitalNew()
     * Nuevo hospital
     */
    public function hospitalNew()
    {
        $rules = [
            "nombre" => "required|is_unique[hospitales.nombre]|min_length[4]"
        ];

        $messages = [
            "nombre" => [
                "required" => "nombre obligatorio",
                "is_unique" => "Este nombre ya existe en la tabla",

            ]
        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {

            $emp = new HospitalModel();

            $data['nombre'] = $this->request->getVar("nombre");
            $data['foto'] = $this->request->getVar("foto");
            $data['creado_por'] = $this->request->getVar("creado_por");

            $emp->save($data);

            $response = [
                'status' => 200,
                'error' => false,
                'message' => 'Hospital added successfully',
                'data' => []
            ];
        }

        return $this->respond($response);
    }


    /**
     * hospitalesList()
     * Lista de todos los hospitales
     */
    public function hospitalesList()
    {
        $emp = new HospitalModel();

        $response = [
            'status' => 200,
            "error" => false,
            'messages' => 'Hospital list',
            'Environment' => ENVIRONMENT,
            'data' => $emp->findAll()
        ];

        return $this->respond($response);
    }

    /**
     * hospitalSearch(id_Hospital)
     * Datos de un hospital
     */
    public function hospitalSearch($id_Hospital)
    {
        $emp = new HospitalModel();

        $data = $emp->find($id_Hospital);

        if (!empty($data)) {

            $response = [
                'status' => 200,
                "error" => false,
                'messages' => 'Single Hospital data',
                'data' => $data
            ];
        } else {

            $response = [
                'status' => 500,
                "error" => true,
                'messages' => 'No Hospital found',
                'data' => []
            ];
        }

        return $this->respond($response);
    }


    /**
     * hospitalUpdate(id)
     * Modifica Hospital
     */
    public function hospitalUpdate($hos_id)
    {
        $rules = [
            "nombre" => "required" 
        ];

        $messages = [
            "nombre" => [
                "required" => "Nombre is required"
            ] 
        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {

            $hospital = new HospitalModel();

            if ($hospital->find($hos_id)) {

                $data['nombre'] = $this->request->getVar("nombre"); 
                $data['foto'] = $this->request->getVar("foto"); 
                $data['creado_por'] = $this->request->getVar("creado_por");                 

                $hospital->update($hos_id, $data);

                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'Hospital updated successfully',
                    'data' => []
                ];
            } else {

                $response = [
                    'status' => 500,
                    "error" => true,
                    'messages' => 'No Hospital found',
                    'data' => []
                ];
            }
        }

        return $this->respond($response);
    }

 
    /**
     * hospitalDelete(id)
     * Borrar Hospital
     */
    public function hospitalDelete($hos_id)
    {
        $hospital = new HospitalModel();

        $data = $hospital->find($hos_id);

        if (!empty($data)) {

            $hospital->delete($hos_id);

            $response = [
                'status' => 200,
                "error" => false,
                'messages' => 'Hospital deleted successfully',
                'data' => []
            ];
        } else {

            $response = [
                'status' => 500,
                "error" => true,
                'messages' => 'No Hospital found',
                'data' => []
            ];
        }

        return $this->respond($response);
    } 
}
