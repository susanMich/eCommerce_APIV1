<?php
namespace App\Controllers;
use App\Models\CompraModel;
use App\Models\DetalleCompraModel;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Compra extends BaseController
{

    public function search()
    {
        try {
            $token = $this->getTokenfromRequest();
            $establecimiento_id = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $input = $this->getRequestInput($this->request);

        if(!isset($input['busq']))
            return $this->sendBadRequest('Parámetro Texto a Buscar requerido ');

        try{
            $db = db_connect($this->getCadenaConexion());
            $SQL="SELECT c.*, p.nombre as proveedor, p.direccion, p.telefono, p.correo, p.gerente, p.tipo
                    FROM tbcompra c inner join tbproveedor p on c.idproveedor = p.id
                    Where c.establecimiento_id=".$establecimiento_id."";
            
            $SQL.=" and (c.numero_comprobante like '%".$db->escapeLikeString($input['busq'])."%' ESCAPE '!' or 
                    p.nombre like '%".$db->escapeLikeString($input['busq'])."%' ESCAPE '!' or
                    p.gerente like '%".$db->escapeLikeString($input['busq'])."%' ESCAPE '!')";
            
            $SQL.=" order by c.fecha desc";

            $query = $db->query($SQL);
            $data = $query->getResultArray();
            $db->close();
            return $this->sendResponse(['Compra' => $data]);
        } 
         catch (Exception $e) {
            if(strpos($e->getMessage(), "doesn't exist") !== false){
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function searchbycode($id)
    {
        if(!isset($id))         return $this->sendBadRequest('Parámetro ID requerido');
        if(!is_numeric($id))    return $this->sendBadRequest('Parámetro ID numérico');
        if($id<1)                return $this->sendBadRequest('Parámetro ID numérico debe ser mayor a 0');
        
        try {
            $token = $this->getTokenfromRequest();
            $establecimiento_id = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try{
            $db = db_connect($this->getCadenaConexion());
            
            $SQL="SELECT c.*, p.nombre as proveedor, p.direccion, p.telefono, p.correo, p.gerente, p.tipo
            FROM tbcompra c inner join tbproveedor p on c.idproveedor = p.id
            Where c.establecimiento_id=".$establecimiento_id." and c.id = ".$id;
            
            $query = $db->query($SQL);
            $encabezadoCompra = $query->getResultArray();

            $SQL="SELECT dc.*, p.barcode, p.descripcion
            FROM tbcompra c inner join tbdetallecompra dc on c.id=dc.idcompra inner join tbproducto p on dc.producto_id=p.id
            Where c.establecimiento_id=".$establecimiento_id." and c.id=".$id;
            
            $query = $db->query($SQL);
            $detalleCompra = $query->getResultArray();
            $db->close();

            $compra['encabezado'] = $encabezadoCompra;
            $compra['detalle'] = $detalleCompra;

            return $this->sendResponse(['Compra' => $compra]);
        } 
         catch (Exception $e) {
            if(strpos($e->getMessage(), "doesn't exist") !== false){
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveCompra()
    {
        $input = $this->getRequestInput($this->request);
        try { 
            $token = $this->getTokenfromRequest();
            $input['usrregistro'] = $token->idusr;
            $input['establecimiento_id'] = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()],ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $validacion = $this->validateEncabeCompra($input);
        if ($validacion != null) return $validacion;
        
        $numItem = 1;

        foreach ($input['detalle'] as $detalle){
            $valItem = $this->validaDetalleCompra($detalle, $numItem++);
            if($valItem != null) return $valItem;
        };

        try{
            $db = db_connect($this->getCadenaConexion());
            $model = new CompraModel($db);
            if($model->saveCompra($input))
                return $this->sendResponse(['message' => 'Comprobante de compra creado correctamente. ID: '.$model->getInsertID()]);
            else
                return $this->sendResponse(['error' => 'Error al Registrar en la BD el comprobante de compra.'],ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            if(strpos($e->getMessage(), "doesn't exist") !== false){
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()],ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function validaDetalleCompra($input, $numItem){
        $rules = [
            'producto_id' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => ['required' => 'ID de producto requerido del Item '.$numItem,
                            'numeric' => 'ID de producto debe ser un valor numérico',
                            'greater_than' => 'ID de producto debe ser mayor a cero'],
            ],
            'stock' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => ['required' => 'Cantidad requerida del Item '.$numItem, 'numeric' => 'Cantidad deber ser numérica del Item '.$numItem,
                                'greater_than' => 'Cantidad deber ser mayor a 0 del Item '.$numItem],
            ],
            'fechavencimiento' => [
                'rules'  => 'required|valid_date',
                'errors' => ['required' => 'Fecha de vencimiento del producto requerida del Item '.$numItem,
                            'valid_date' => 'Se requiere una fecha de vencimiento válida del Item '.$numItem],
            ],
           'costo' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => ['required' => 'Costo del producto requerido del Item '.$numItem, 'numeric' => 'Precio del producto deber ser numérica del Item '.$numItem,
                             'greater_than' => 'Precio del producto deber ser mayor a 0 del Item '.$numItem],
            ],
           'precio_minorista' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => ['required' => 'Precio minorista del Producto requerida del Item '.$numItem, 'numeric' => 'Precio minorista del Producto deber ser numérica del Item '.$numItem,
                            'greater_than' => 'Precio minorista del Producto deber ser mayor a 0 del Item '.$numItem],
            ],
            'precio_mayorista' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => ['required' => 'Precio mayorista del Producto requerida del Item '.$numItem, 'numeric' => 'Precio mayorista del Producto deber ser numérica del Item '.$numItem,
                            'greater_than' => 'Precio mayorista del Producto deber ser mayor a 0 del Item '.$numItem],
            ],
            'descuento' => [
                'rules'  => 'required|numeric|greater_than_equal_to[0]',
                'errors' => ['required' => 'Descuento del Producto requerido del Item '.$numItem, 'numeric' => 'Descuento del Producto deber ser numérica del Item '.$numItem,
                             'greater_than_equal_to' => 'Descuento del Producto deber ser mayor o igual 0 del Item '.$numItem],
            ],
            'iva' => [
                'rules'  => 'required|numeric',
                'errors' => ['required' => 'IVA requerido del Item '.$numItem],
            ],
        ];
        
        if (!$this->validateRequest($input, $rules)) 
            return $this->sendResponse(['validaciones' => $this->getErrorsAsArray($this->validator->getErrors())],ResponseInterface::HTTP_BAD_REQUEST);    
        else{
            if($input['descuento'] >= $input['costo']){
                $errorsAsArray =  array();
                $errorsAsArray[0]['campo'] = 'descuento';
                $errorsAsArray[0]['mensaje'] = 'Descuento no puede ser mayor o igual al precio de costo del Producto Item ' + $numItem;
                return $this->sendResponse(['validaciones' => $errorsAsArray],ResponseInterface::HTTP_BAD_REQUEST);    
            }else{
                return null;
            }
        }
    }

    private function validateEncabeCompra($input)
    {
        $rules = [
            'fecha' => [
                'rules'  => 'required|valid_date',
                'errors' => ['required' => 'Fecha de la Factura requerida',
                            'valid_date' => 'Se requiere una fecha válida'],
            ],
            'idproveedor' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => ['required' => 'ID de proveedor requerido',
                            'numeric' => 'ID de proveedor debe ser un valor numérico',
                             'greater_than' => 'ID de proveedor debe ser mayor a cero'],
            ],
        ];
        if (!$this->validateRequest($input, $rules)) 
            return $this->sendResponse(['validaciones' => $this->getErrorsAsArray($this->validator->getErrors())],ResponseInterface::HTTP_BAD_REQUEST);    
        else
            return null;
    }

    public function editarCompra($id)
    {
        if(!isset($id))         return $this->sendBadRequest('Parámetro ID requerido');
        if(!is_numeric($id))    return $this->sendBadRequest('Parámetro ID numérico');
        if($id<1)                return $this->sendBadRequest('Parámetro ID numérico debe ser mayor a 0');

        $input = $this->getRequestInput($this->request);
        try { 
            $token = $this->getTokenfromRequest();
            $input['usrmodifico'] = $token->idusr;
            $input['establecimiento_id'] = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()],ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try{ //Si no lo encuentra lanza exception
            $db = db_connect($this->getCadenaConexion());
            $model = new CompraModel($db);
            $model->findCompraById($id); 
        } catch (Exception $e) {
            if(strpos($e->getMessage(), "doesn't exist") !== false){
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendBadRequest('Compra a actualizar no existe');
        }

        $validacion = $this->validateEncabeCompra($input);
        if ($validacion != null) return $validacion;

        $numItem = 1;

        foreach ($input['detalle'] as $detalle){
            $valItem = $this->validaDetalleCompra($detalle, $numItem++);
            if($valItem != null) return $valItem;
        };

        try{
            if($model->editarCompra($id, $input))
                return $this->sendResponse(['message' => 'Comprobante de compra editado correctamente.']);
            else
                return $this->sendResponse(['error' => 'Error al editar en la BD el comprobante de compra.'],ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()],ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}