<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\Services;
/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    public function sendBadRequest(string $msg, $tipo='error'){ 
         return $this->sendResponse([$tipo => $msg], ResponseInterface::HTTP_BAD_REQUEST);  
    }

    public function sendResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK){
        return $this->response->setStatusCode($code)->setJSON($responseBody);
    }

    public function getRequestInput(IncomingRequest $request){
        $input = $request->getPost();
        if (empty($input)) {
            $input = json_decode($request->getBody(), true);
            if (empty($input)) {
                $input = $request->getGet();
            }
        }
        return $input;
    }

    public function validateRequest($input, array $rules){
        $this->validator = Services::Validation()->setRules($rules);
        return $this->validator->run($input);
    }

    public function validateTokenfromRequest(){
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        try {  
            helper('jwt');
            $decodedToken = validateToken(getTokenFromRequest($authenticationHeader));
            return $decodedToken;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    
 

    public function getErrorsAsArray($errors)
    {
        $errorsAsArray =  array();
        $i = 0;
        foreach ($errors as $clave => $valor) {
            $errorsAsArray[$i]['campo'] = $clave;
            $errorsAsArray[$i++]['mensaje'] = $valor;
        }
        return $errorsAsArray;
    }

    public function is_unique_correo($str, $id = -1)
    {
        $db = db_connect();
        if($id < 0){
            // estoy registrando id = -1
            $query = $db->query('SELECT * FROM tbusuario u WHERE u.correo = ?', array($str));
        }else{
            // estoy modificando id > 0
            $query = $db->query('SELECT * FROM tbusuario u WHERE u.correo = ? and u.id != ?', array($str, $id));
        }
        $data = $query->getResultArray();
        $db->close();
        if (count($data) >  0)
            return false;
        else
            return true;
    }
}
