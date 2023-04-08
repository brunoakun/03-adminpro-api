<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MedicoModel;

class MedicoController extends ResourceController
{

    /**
     * medicoNew()
     * Nuevo medico
     */
    public function medicoNew()
    {
        $rules = [
            "nombre" => "required|is_unique[medicos.nombre]|min_length[4]"
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

            $emp = new MedicoModel();

            $data['nombre'] = $this->request->getVar("nombre");
            $data['foto'] = $this->request->getVar("foto");
            $data['creado_por'] = $this->request->getVar("creado_por");

            $emp->save($data);

            $response = [
                'status' => 200,
                'error' => false,
                'message' => 'medico added successfully',
                'data' => []
            ];
        }

        return $this->respond($response);
    }


    /**
     * medicosList()
     * Lista de todos los medicos
     */
    public function medicosList()
    {
        $emp = new medicoModel();

        $response = [
            'status' => 200,
            "error" => false,
            'messages' => 'medico list',
            'Environment' => ENVIRONMENT,
            'data' => $emp->findAll()
        ];

        return $this->respond($response);
    }

    /**
     * medicoSearch(id_medico)
     * Datos de un medico
     */
    public function medicoSearch($id_medico)
    {
        $emp = new MedicoModel();

        $data = $emp->find($id_medico);

        if (!empty($data)) {

            $response = [
                'status' => 200,
                "error" => false,
                'messages' => 'Single medico data',
                'data' => $data
            ];
        } else {

            $response = [
                'status' => 500,
                "error" => true,
                'messages' => 'No medico found',
                'data' => []
            ];
        }

        return $this->respond($response);
    }


    /**
     * medicoUpdate(id)
     * Modifica medico
     */
    public function medicoUpdate($hos_id)
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

            $medico = new MedicoModel();

            if ($medico->find($hos_id)) {

                $data['nombre'] = $this->request->getVar("nombre"); 
                $data['foto'] = $this->request->getVar("foto"); 
                $data['creado_por'] = $this->request->getVar("creado_por");                 

                $medico->update($hos_id, $data);

                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => 'medico updated successfully',
                    'data' => []
                ];
            } else {

                $response = [
                    'status' => 500,
                    "error" => true,
                    'messages' => 'No medico found',
                    'data' => []
                ];
            }
        }

        return $this->respond($response);
    }

 
    /**
     * medicoDelete(id)
     * Borrar medico
     */
    public function medicoDelete($hos_id)
    {
        $medico = new MedicoModel();

        $data = $medico->find($hos_id);

        if (!empty($data)) {

            $medico->delete($hos_id);

            $response = [
                'status' => 200,
                "error" => false,
                'messages' => 'medico deleted successfully',
                'data' => []
            ];
        } else {

            $response = [
                'status' => 500,
                "error" => true,
                'messages' => 'No medico found',
                'data' => []
            ];
        }

        return $this->respond($response);
    } 
}
