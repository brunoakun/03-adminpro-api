<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use \App\Validation\CustomRules;
use Config\Services;

class UserController extends ResourceController
{


    //////////////// AUTH ////////////////////////


    /**
     * register(username,email,password)
     */

    public function register()
    {
        $rules = [
            "username" => "required|is_unique[users.username]|min_length[4]",
            "email" => "required|valid_email|is_unique[users.email]|min_length[6]",
            // "phone_no" => "required|mobileValidation[phone_no]",
            "password" => "required|min_length[4]",
            // "rol" => "required",
        ];

        $messages = [
            "username" => [
                "required" => "Nombre obligatorio",
                "is_unique" => "Este usuario ya existe"
            ],
            "email" => [
                "is_unique" => "Este eMail ya existe",
                "valid_email" => "El eMail no parece tener un formato válido"
            ],
            "telefono" => [
                "mobileValidation" => "El teléfono no parece tener un formato correcto"
            ],
            "password" => [
                "required" => "password obligatorio"
            ],
        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'mensaje' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {

            $userModel = new UserModel();

            // rol por defecto
            $rol = $this->request->getVar("rol");
            if (!$rol) $rol = 'usuario';

            $data = [
                "username" => $this->request->getVar("username"),
                "password" => password_hash($this->request->getVar("password"), PASSWORD_DEFAULT),
                "rol" => $rol,
                "email" => $this->request->getVar("email"),
                "nombre" => $this->request->getVar("nombre"),
                "telefono" => $this->request->getVar("telefono"),
            ];

            if ($userModel->insert($data)) {

                $response = [
                    'status' => 200,
                    "error" => false,
                    'mensaje' => 'Usuario dado de alta satisfactoriamente',
                    'data' => []
                ];
            } else {

                $response = [
                    'status' => 500,
                    "error" => true,
                    'mensaje' => 'Fallo en la creación de usuario',
                    'data' => []
                ];
            }
        }

        return $this->respond($response);
    }


    /**
     * logIn(username,password)
     */
    public function login()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, OPTIONS");

        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $messages = [
            "username" => [
                "required" => "Usuario obligatorio"
            ],
            "password" => [
                "required" => "Password requerido"
            ],
        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'mensaje' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {
            $userModel = new UserModel();
            $userdata = $userModel->where("username", $this->request->getVar("username"))->first();

            if (!empty($userdata)) {

                if (password_verify($this->request->getVar("password"), $userdata['password'])) {

                    $key = getenv('JWT_SECRET');

                    $iat = time(); // current timestamp value
                    $nbf = $iat + 0;
                    $exp = $iat + getenv('JWT_EXPIRE');  // 1 hora

                    $payload = array(
                        "iat" => $iat, // issued at
                        "nbf" => $nbf, // not before in seconds (NO se permite nuevo acceso en x segundos)
                        "exp" => $exp, // expire time in seconds
                        "data" => array(
                            'id' => $userdata['id'],
                            'username' => $userdata['username'],
                            'role' => $userdata['rol']
                        ),
                    );

                    $token = JWT::encode($payload, $key, 'HS256');
                    $response = [
                        'status' => 200,
                        'error' => false,
                        'mensaje' =>  'Usuario loggeado correctamente',
                        'data' => [
                            'token' => $token,
                            "exp" => $exp,
                            "time" => time(),
                            'userdata' => $userdata
                        ]
                    ];
                } else {

                    $response = [
                        'status' => 500,
                        'error' => true,
                        'mensaje' => 'Credenciales incorrectas',
                        'data' => []
                    ];
                }
            } else {
                $response = [
                    'status' => 500,
                    'error' => true,
                    'mensaje' => 'Usuario no encontrado',
                    'data' => []
                ];
            }
        }
        return $this->respond($response);
    }


    /**
     * refreshToken(token)
     * Actualiza el token del usuario
     */
    public function refreshToken()
    {
        $key = getenv('JWT_SECRET');
        $iat = time();
        $nbf = $iat + 0;
        $exp = $iat + getenv('JWT_EXPIRE');

        try {
            $token = $this->request->getServer("HTTP_AUTHORIZATION");
            $token = str_replace('Bearer ', '', $token);
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            if ($decoded) {
                $payload = array(
                    "iat" => $iat, // issued at
                    "nbf" => $nbf, // not before in seconds (NO se permite nuevo acceso en x segundos)
                    "exp" => $exp, // expire time in seconds
                    'data' => $decoded->data

                );

                $newToken = JWT::encode($payload, $key, 'HS256');
                $userModel = new UserModel();
                $userdata = $userModel->where("username", $decoded->data->username)->first();

                $response = [
                    'status' => 200,
                    'error' => false,
                    'mensaje' =>  'Token actualizado',
                    'data' => [
                        'token' => $newToken,
                        'userdata' => $userdata
                    ]
                ];
            }
        } catch (Exception $ex) {
            $response = [
                'status' => 401,
                'error' => true,
                'mensaje' => 'Acceso denegado ' . $ex,
                'data' => []
            ];
        }
        return $this->respond($response);
    }


    /**
     * details(token)
     * Devuelve la info del token
     */
    public function details()
    {
        $key = getenv('JWT_SECRET');

        try {
            //$header = $this->request->getHeader("Authorization");
            $token = $this->request->getServer("HTTP_AUTHORIZATION");
            $token = str_replace('Bearer ', '', $token);
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            if ($decoded) {
                $response = [
                    'status' => 200,
                    'error' => false,
                    'mensaje' => 'Datos del token',
                    'data' => [
                        'profile' => $decoded->data
                    ]
                ];
            }
        } catch (Exception $ex) {
            $response = [
                'status' => 401,
                'error' => true,
                'mensaje' => 'Acceso denegado ' . $ex,
                'data' => []
            ];
        }
        return $this->respond($response);
    }


    /**
     * 
     *  //////////////////////////////// CRUD ////////////////////////////
     * 
     */

     
    /**
     * userDetall(id)
     * Datos de un usuario
     */
    public function userDetall($id)
    {
        $user = new UserModel();
        $data = $user->find($id);

        if (!empty($data)) {

            $response = [
                'status' => 200,
                "error" => false,
                'mensaje' => 'Datos de 1 usuario',
                'data' => $data
            ];
        } else {
            $response = [
                'status' => 500,
                "error" => true,
                'mensaje' => 'Usuario no encontrado',
                'data' => []
            ];
        }

        return $this->respond($response);
    }


    /**
     * usrList()
     * Lista todos los usuarios 
     */
    public function usrList()
    {
        $list = new UserModel();
        try {
            $response = [
                'status' => 200,
                "error" => false,
                'mensaje' => 'usr List',
                'Environment' => ENVIRONMENT,
                'data' => $list->findAll()
            ];
        } catch (Exception $ex) {
            $response = [
                'status' => 200,
                'error' => true,
                'mensaje' => 'error',
                'data' => ["{'Exception ':$ex}"]
            ];
        }

        return $this->respond($response);
    }


    /**
     * userUpdate(id)
     * Modifica Usuario
     */
    public function userUpdate($id)
    {
        $user = new UserModel();

        $rules = [
            "username"   => "required|is_unique[users.username,id,$id]|min_length[4]",
            "nombre"   => "required|min_length[4]",
            "email" => "required|valid_email|emailUnicoUsr[$id]|min_length[6]",
            "password" => "permit_empty|min_length[4]",
            // "rol" => "required",
            "telefono" => "permit_empty|mobileValidation[telefono]",
        ];

        $messages = [
            "username" => [
                "required" => "Usurio obligatorio",
                "is_unique" => "Este usuario ya existe"
            ],
            "email" => [
                "emailUnicoUsr" => "Este eMail ya existe para otro usuario",
                "valid_email" => "El eMail no parece tener un formato válido"
            ],
            "telefono" => [
                "mobileValidation" => "El teléfono no parece tener un formato correcto"
            ]
        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'mensaje' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {

            if ($user->find($id)) {
                $data['username'] = $this->request->getVar("username");
                $data['nombre'] = $this->request->getVar("nombre");
                $data['email'] = $this->request->getVar("email");
                if ($this->request->getVar("telefono")) $data['telefono'] = $this->request->getVar("telefono");
                if ($this->request->getVar("foto")) $data['foto'] = $this->request->getVar("foto");
                if ($this->request->getVar("password")) $data['password'] = password_hash($this->request->getVar("password"), PASSWORD_DEFAULT);

                $user->update($id, $data);

                $response = [
                    'status' => 200,
                    'error' => false,
                    'mensaje' => 'Usuario actualizado',
                    'data' => []
                ];
            } else {

                $response = [
                    'status' => 500,
                    "error" => true,
                    'mensaje' => 'Usuario no encontrado',
                    'data' => []
                ];
            }
        }

        return $this->respond($response);
    }




    /**
     * userDelete(id)
     * Borrar Usuario
     */
    public function userDelete($id)
    {
        $user = new UserModel();

        $data = $user->find($id);

        if (!empty($data)) {

            $user->delete($id);
            $this->borraImagenes($id);

            $response = [
                'status' => 200,
                "error" => false,
                'mensaje' => 'Usuario eliminado',
                'data' => []
            ];
        } else {

            $response = [
                'status' => 500,
                "error" => true,
                'mensaje' => 'Usuario no encontrado',
                'data' => []
            ];
        }

        return $this->respond($response);
    }


    /**
     * imgUpload()
     * Sube una imágen
     * En el cuerpo de la solicitud, incluye el archivo de imagen que deseas cargar en el formato multipart/form-data.
     */

    public function imgUpload()
    {
        $user = new UserModel();

        $id = $this->request->getVar("id");
        $archivo = $this->request->getFile('imagen');

        $rules = [
            'id' => "required|is_not_unique[users.id]",
            'imagen' => 'uploaded[imagen]|max_size[imagen,1000000]|mime_in[imagen,image/jpeg,image/png,image/gif]',
        ];

        $messages = [
            "imagen" => [
                "max_size" => "Esta imágen es demasiado grande, tamaño máximo es de 1Mb.",
                "mime_in" => "Este fichero no es una imágen válida"
            ]
        ];

        $validation = Services::validation();
        $validation->setRules($rules, $messages);

        if (!$validation->withRequest($this->request)->run()) {
            $response = [
                'status' => 200,
                'error' => true,
                'mensaje' => $validation->getErrors(),
                'data' => []
            ];
        } else {
            try {

                $ruta_destino = 'public/data/fotos';
                $nombre_original = $archivo->getName();
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nuevo_nombre = $id . '_' . time() . '.' . $extension;

                $archivo->move(ROOTPATH . $ruta_destino, $nuevo_nombre);

                // Actualizar tabla
                $data['foto'] = $nuevo_nombre;
                $user->update($id, $data);

                $response = [
                    'status' => 200,
                    'error' => false,
                    'mensaje' => 'Archivo cargado correctamente.',
                    'data' => [
                        'nombre_original' => $nombre_original,
                        'guardado_como' => $nuevo_nombre
                    ]
                ];
            } catch (Exception $ex) {
                $response = [
                    'status' => 200,
                    'error' => true,
                    'mensaje' => 'Error al cargar el archivo.',
                    'data' => ['exception' => $ex->getMessage()]
                ];
            }
        }

        return $this->respond($response);
    }



    /**
     * userDeleteFoto(id)
     * Borrar foto actual de 1 Usuario
     */
    public function userDeleteFoto($id)
    {
        $user = new UserModel();

        $data = $user->find($id);

        if (!empty($data)) {

            // Borrar foto actual
            // $dir = ROOTPATH . 'public/data/fotos';
            // $file  = $dir . '/' . $data['foto'];
            // if (is_file($file)) unlink($file);

            // Actualizar tabla
            $data['foto'] = '_noUsr.png';
            $user->update($id, $data);

            $response = [
                'status' => 200,
                "error" => false,
                'mensaje' => 'Foto eliminada',
                'data' => []
            ];
        } else {

            $response = [
                'status' => 500,
                "error" => true,
                'mensaje' => 'Usuario no encontrado',
                'data' => []
            ];
        }

        return $this->respond($response);
    }


    ////////////////// Métodos AUX  //////////////


    /**
     * existeEmail($buscar:string)
     * return: bool
     */
    public function existeemail($buscar)
    {
        $user = new UserModel();
        $count = $user
            ->where('email', $buscar)
            ->countAllResults();

        $response = [
            'status' => 200,
            "error" => false,
            'message' => "comprobar si existe eMail $buscar",
            'data' => $count
        ];
        return $this->respond($response);
    }

    /**
     * borraImagenes(id):void
     * Elimina todas la imágenes de un usuario
     */
    public function borraImagenes($id)
    {
        $dir = ROOTPATH . 'public/data/fotos';
        $files = glob($dir . '/' . $id . '_*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return;
    }
}
