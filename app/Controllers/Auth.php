<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use ReflectionException;
use Config\Services;

class Auth extends BaseController
{

    private function validaParamsLogin($input)
    {

        $validator = Services::Validation();
        if (!isset($input['usuario']))  return "Se requiere usuario";
        if (!isset($input['clave']))   return "Se requiere clave";
        return "";
    }

    public function login()
    {
        $input = $this->getRequestInput($this->request);
        $resp = $this->validaParamsLogin($input);
        if ($resp != "") return $this->sendBadRequest($resp);

        $userModel = new UserModel();
        $user = $userModel->findUserByUsername($input['usuario']);
        if (!$user) return $this->sendBadRequest("No existe usuario con el nombre: " . $input['usuario']);
        if (!password_verify($input['clave'], $user['clave']))  return $this->sendBadRequest("Clave Incorrecta");
       

        try {
            helper('jwt');
            $token = genToken($input['usuario'], $user['id']);
            return $this->sendResponse(['user' => $input['usuario'],
                                      'access_token' => $token ]);
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function validateToken()
    {
        try {
            $decodedToken = $this->validateTokenfromRequest();
            $tiempo_exp =  ($decodedToken->exp - time()) / 60;
            return $this->sendResponse(['code' => 1, 
                          'message' => 'Token verificado!!. Usuario: ' . $decodedToken->usuario.
                          ' expira en: ' . round($tiempo_exp,2) . ' minutos']);
        } catch (Exception $e) {
            return $this->sendBadRequest($e->getMessage());
        }   
    }
}
