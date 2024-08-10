<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Producto extends BaseController
{
    
    // GET /productos
    public function index()
    {
        $model = new ProductoModel();
        try{
            $productos = $model->findAll();
            return $this->sendResponse($productos);
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // GET /productos/1
    public function show($id = null)
    {
        try{
            
            $model = new ProductoModel();
            $producto = $model->find($id);
            if (!$producto) {
                return  $this->sendBadRequest('Producto no encontrado.');
            }
            return $this->sendResponse($producto);

        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    // POST /productos
    public function create()
    {
        $data = $this->request->getPost();
        $model = new ProductoModel();
        if (!$model->insert($data)) {
            return $this->sendBadRequest("Error al insertar producto");
        }
        return $this->sendResponse($model->find($model->insertID()));
    }

    // PUT /productos/1
    public function update($id = null)
    {
       try{
             $model = new ProductoModel();
             $input = json_decode($this->request->getBody(), true);

            if (!$model->find($id)) {
                return $this->sendBadRequest('Producto no encontrado.');
            }

            if (!$model->update($id, $input)) {
                return $this->sendBadRequest("Error al actualizar producto");
            }
            return $this->sendResponse($model->find($id));
        
            

       } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    // DELETE /productos/1
    public function delete($id = null)
    {
        $model = new ProductoModel();
        if (!$model->find($id)) {
            return $this->sendBadRequest('Producto no encontrado.');
        }
        $model->delete($id);
        return $this->sendResponse(['id' => $id, 'message' => 'Producto eliminado.']);
    }
    
    
    
}
