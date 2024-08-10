<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Cliente extends BaseController
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

        if (!isset($input['fuente']))
            return $this->sendBadRequest('Parámetro Fuente requerido ');
        if (!is_numeric($input['fuente']))
            return $this->sendBadRequest('Parámetro Fuente numérico');
        if ($input['fuente'] != 1) {
            if (!isset($input['tipoid']) and !isset($input['genero']) and !isset($input['tipocliente']))
                return $this->sendBadRequest('Debe escoger un filtro para enlistar todos los clientes');
            if (!($input['tipoid'] > 0 || $input['genero'] > 0 || $input['tipocliente'] > 0))
                return $this->sendBadRequest('Debe escoger un filtro para enlistar todos los clientes');
        }

        try {
            $db = db_connect($this->getCadenaConexion());
            $SQL = "SELECT c.*, g.descripcion as c_genero, ti.descripcion as c_tipoidentificacion, o.descripcion as c_operadora, tc.descripcion as c_tipocliente,
            ec.descripcion as c_estado
            FROM tbcliente c
            inner join tbtipoidentificacion ti on c.tipoidentificacion = ti.id 
            left join tbtipocliente tc on c.tipo = tc.id
            left join tbgenero g on g.id=c.genero
            left join tbestadocliente ec on c.estado = ec.id
            left join tboperadora o on  c.operadora=o.id where c.establecimiento_id=".$establecimiento_id." and c.id>0";

            if ($input['fuente'] == 1) $SQL .= " and top>0";
            if (isset($input['busq']) and $input['busq'] != "")
                $SQL .= " and (c.nombre like'" . $db->escapeLikeString($input['busq']) . "%' ESCAPE '!' or 
                                c.identificacion like '" . $db->escapeLikeString($input['busq']) . "%' ESCAPE '!' )";

            if (isset($input['tipoid']) and $input['tipoid'] > 0) $SQL .= " and ti.id=" . $input['tipoid'];
            if (isset($input['gen']) and $input['gen'] > 0) $SQL .= " and g.id=" . $input['gen'];
            if (isset($input['tipocliente']) and $input['tipocliente'] > 0) $SQL .= " and tc.id=" . $input['tipocliente'];
            if (isset($input['estado']) and $input['estado'] > 0) $SQL .= " and c.estado=" . $input['estado'];

            $SQL .= " order by ";

            if (isset($input['orden']) and is_numeric($input['orden'])) {
                switch ($input['orden']) {
                    case 1:
                        $SQL .= "c.fechacreacion";
                        break;
                    case 2:
                        $SQL .= "c_tipocliente";
                        break;
                    case 3:
                        $SQL .= "c.top";
                        break;
                    case 4:
                        $SQL .= "c.genero";
                        break;
                    case 5:
                        $SQL .= "c.tipoidentificacion";
                        break;
                    case 6:
                        $SQL .= "c.nombre";
                        break;
                    default:
                        $SQL .= "c.fechacreacion";
                }
            } else
                $SQL .= "c.fechacreacion";

            if (isset($input['modoorden']) and is_numeric($input['modoorden']))
                $SQL .= " " . $input['modoorden'] == 1 ? " asc" : " desc";
            else
                $SQL .= " asc";

            $query = $db->query($SQL);
            $data = $query->getResultArray();
            $db->close();
            return $this->sendResponse(['clientes' => $data]);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function searchtoselect()
    {
        try {
            $token = $this->getTokenfromRequest();
            $establecimiento_id = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $input = $this->getRequestInput($this->request);

        if (!isset($input['busq']))
            return $this->sendBadRequest('Parámetro Texto requerido ');
        if ($input['busq'] == "")
            return $this->sendBadRequest('Parámetro Texto no debe ser en blanco');

        try {
            $db = db_connect($this->getCadenaConexion());
            $SQL = "SELECT c.*, g.descripcion as c_genero, ti.descripcion as c_tipoidentificacion, o.descripcion as c_operadora, tc.descripcion as c_tipocliente,
            ec.descripcion as c_estado
            FROM tbcliente c
            inner join tbtipoidentificacion ti on c.tipoidentificacion = ti.id 
            left join tbtipocliente tc on c.tipo = tc.id
            left join tbgenero g on g.id=c.genero
            left join tbestadocliente ec on c.estado = ec.id
            left join tboperadora o on  c.operadora=o.id where c.establecimiento_id=".$establecimiento_id." and c.id>0";

            $SQL .= " and (c.nombre like'%" . $db->escapeLikeString($input['busq']) . "%' ESCAPE '!' or 
                                c.identificacion like '%" . $db->escapeLikeString($input['busq']) . "%' ESCAPE '!' )";

            $SQL .= " order by ";

            if (isset($input['orden']) and is_numeric($input['orden'])) {
                switch ($input['orden']) {
                    case 1:
                        $SQL .= "c.fechacreacion";
                        break;
                    case 2:
                        $SQL .= "c_tipocliente";
                        break;
                    case 3:
                        $SQL .= "c.top";
                        break;
                    case 4:
                        $SQL .= "c.genero";
                        break;
                    case 5:
                        $SQL .= "c.tipoidentificacion";
                        break;
                    case 6:
                        $SQL .= "c.nombre";
                        break;
                    default:
                        $SQL .= "c.nombre";
                }
            } else
                $SQL .= "c.fechacreacion";

            if (isset($input['modoorden']) and is_numeric($input['modoorden']))
                $SQL .= " " . $input['modoorden'] == 1 ? " asc" : " desc";
            else
                $SQL .= " asc";

            $query = $db->query($SQL);
            $data = $query->getResultArray();
            $db->close();
            return $this->sendResponse(['clientes' => $data]);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getClienteById($id)
    {
        try {
            $token = $this->getTokenfromRequest();
            $establecimiento_id = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $db = db_connect($this->getCadenaConexion());
            $SQL = "SELECT c.*, ti.descripcion as c_tipoidentificacion, o.descripcion as c_operadora, tc.descripcion as c_tipocliente,
            ec.descripcion as c_estado
            FROM tbcliente c, tbtipoidentificacion ti, tboperadora o, tbtipocliente tc, tbestadocliente ec
            where c.establecimiento_id=".$establecimiento_id." and c.tipoidentificacion = ti.id and c.operadora=o.id and c.tipo = tc.id and c.estado = ec.id and c.id=" . $id;

            $query = $db->query($SQL);
            $cliente = $query->getRowArray();

            $db->close();
            return $cliente;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            throw new Exception('Error al consultar cliente ID: ' . $id . ". Desc: " . $e->getMessage());
        }
    }

    public function is_unique_identificacion($str, $id, $establecimiento_id)
    {
        $db = db_connect($this->getCadenaConexion());
        if($id < 0){
            // estoy registrando id = -1
            $query = $db->query('SELECT * FROM tbcliente cl WHERE cl.identificacion = ? and cl.establecimiento_id = ?', array($str, $establecimiento_id));
        }else{
            // estoy modificando id > 0
            $query = $db->query('SELECT * FROM tbcliente cl WHERE cl.identificacion = ? and cl.id != ? and cl.establecimiento_id = ?', array($str, $id, $establecimiento_id));
        }
        $data = $query->getResultArray();
        $db->close();
        if (count($data) >  0)
            return false;
        else
            return true;
    }

    public function crearCliente()
    {
        $input = $this->getRequestInput($this->request);

        try {
            $token = $this->getTokenfromRequest();
            $input['usrcreo'] = $token->idusr;
            $establecimiento_id = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (!empty($input['identificacion'])) {
            if (!$this->is_unique_identificacion($input['identificacion'], -1, $establecimiento_id)) {
                return $this->sendResponse(['validaciones' => [['campo' => 'identificacion', 'mensaje'=> 'Identificación debe ser única']]], 404);
            }
        }

        $rules = [
            'nombre' => [
                'rules'  => 'required|max_length[300]',
                'errors' => ['required' => 'Nombre del cliente requerida'],
            ],
            'identificacion' => [
                'rules'  => 'required|max_length[20]',
                'errors' => [
                    'required' => 'Identificación del cliente requerida'
                ],
            ],
            'tipoidentificacion' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => ['required' => 'Tipo de Identificación del cliente requerida'],
            ],
            'direccion' => [
                'rules'  => 'permit_empty|max_length[500]',
                'errors' => ['max_length' => 'Dirección del cliente requerida Max 500 letras'],
            ],
            'correo' => [
                'rules'  => 'permit_empty|valid_email',
                'errors' => ['valid_email' => 'Correo del cliente NO Válido'],
            ],
            'genero' => [
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => 'Género debe ser un número'
                ],
            ],
            'telefonomovil' => [
                'rules'  => 'permit_empty|max_length[20]',
                'errors' => ['max_length' => 'Teléfono del cliente requerido Máximo 20 dígitos'],
            ],
            'operadora' => [
                'rules'  => 'permit_empty|numeric|greater_than[0]',
                'errors' => ['numeric' => 'Operadora del cliente requerida'],
            ],
            'tipo' => [
                'rules'  => 'permit_empty|numeric|greater_than[0]',
                'errors' => ['numeric' => 'Tipo de Cliente requerido'],
            ]
        ];

        if (!$this->validateRequest($input, $rules))
            return $this->sendResponse(['validaciones' => $this->getErrorsAsArray($this->validator->getErrors())], ResponseInterface::HTTP_BAD_REQUEST);

        try {
            $input['establecimiento_id'] = $establecimiento_id;
            $db = db_connect($this->getCadenaConexion());
            $model = new ClienteModel($db);
            $model->insert($input);
            return $this->sendResponse(['message' => 'Cliente creado correctamente. ID: ' . $model->getInsertID()]);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function editarCliente($id)
    {
        if (!isset($id))         return $this->sendBadRequest('Parámetro ID requerido');
        if (!is_numeric($id))    return $this->sendBadRequest('Parámetro ID numérico');
        if ($id < 1)                return $this->sendBadRequest('Parámetro ID numérico mayor a 0');

        try {
            $db = db_connect($this->getCadenaConexion());
            $model = new ClienteModel($db);
            $model->findClienteById($id); //Si no lo encuentra lanza exception
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendBadRequest('cliente a actualizar No existe');
        }

        $input = $this->getRequestInput($this->request);

        try {
            $token = $this->getTokenfromRequest();
            $input['usrmodifico'] = $token->idusr;
            $establecimiento_id = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
        }


        if (!empty($input['identificacion'])) {
            if (!$this->is_unique_identificacion($input['identificacion'], $id, $establecimiento_id)) {
                return $this->sendResponse(['validaciones' => [['campo' => 'identificacion', 'mensaje'=> 'Identificación debe ser única']]], 404);
            }
        }

        $rules = [
            'nombre' => [
                'rules'  => 'required|max_length[300]',
                'errors' => ['required' => 'Nombre del cliente requerida'],
            ],
            'identificacion' => [
                'rules'  => 'permit_empty|max_length[20]',
                'errors' => ['max_length' => 'Identificación del cliente requerido Máximo 20 dígitos'],
            ],
            'tipoidentificacion' => [
                'rules'  => 'permit_empty|numeric|greater_than[0]',
                'errors' => ['numeric' => 'Tipo de Identificación del cliente requerida'],
            ],
            'direccion' => [
                'rules'  => 'permit_empty|max_length[500]',
                'errors' => ['max_length' => 'Dirección del cliente requerida Max 500 letras'],
            ],
            'correo' => [
                'rules'  => 'permit_empty|valid_email',
                'errors' => ['valid_email' => 'Correo del cliente NO Válido']
            ],
            'genero' => [
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => 'Género debe ser un número'
                ],
            ],
            'telefonomovil' => [
                'rules'  => 'permit_empty|max_length[20]',
                'errors' => ['max_length' => 'Teléfono del cliente requerido Máximo 20 dígitos'],
            ],
            'operadora' => [
                'rules'  => 'permit_empty|numeric|greater_than[0]',
                'errors' => ['numeric' => 'Operadora del cliente requerida'],
            ],
            'tipo' => [
                'rules'  => 'permit_empty|numeric|greater_than[0]',
                'errors' => ['numeric' => 'Tipo de Cliente requerido'],
            ],
            'estado' => [
                'rules'  => 'permit_empty|numeric|greater_than[0]',
                'errors' => ['required' => 'Estado de Cliente requerido'],
            ]
        ];

        if (!$this->validateRequest($input, $rules))
            return $this->sendResponse(['validaciones' => $this->getErrorsAsArray($this->validator->getErrors())], ResponseInterface::HTTP_BAD_REQUEST);

        try {
            $input['establecimiento_id'] = $establecimiento_id;
            $model = new ClienteModel($db);
            $model->update($id, $input);
            return $this->sendResponse(['message' => 'Cliente editado correctamente']);
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCliente($id)
    {
        if (!isset($id))         return $this->sendBadRequest('Parámetro ID requerido');
        if (!is_numeric($id))    return $this->sendBadRequest('Parámetro ID numérico');
        if ($id < 1)                return $this->sendBadRequest('Parámetro ID numérico mayor a 0');

        try {
            $token = $this->getTokenfromRequest();
            $establecimiento_id = $token->establecimiento_id;
        } catch (Exception $e) {
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $db = db_connect($this->getCadenaConexion());
            $SQL = "SELECT c.*, g.descripcion as c_genero, ti.descripcion as c_tipoidentificacion, o.descripcion as c_operadora, tc.descripcion as c_tipocliente,
            ec.descripcion as c_estado
            FROM tbcliente c
            inner join tbtipoidentificacion ti on c.tipoidentificacion = ti.id 
            left join tbtipocliente tc on c.tipo = tc.id
            left join tbgenero g on g.id=c.genero
            left join tbestadocliente ec on c.estado = ec.id
            left join tboperadora o on  c.operadora=o.id";

            $SQL .= " where c.establecimiento_id=".$establecimiento_id. " and c.id=" . $id;

            $query = $db->query($SQL);
            $Cliente = $query->getResultArray();
            $db->close();

            if (isset($Cliente) && !empty($Cliente))
                return $this->sendResponse(['Cliente' => $Cliente]);
            else
                return $this->sendResponse(['error' => "Cliente No encontrado, ID: " . $id], ResponseInterface::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function setEstadoCliente($id)
    {
        try {
            $input = $this->getRequestInput($this->request);
            $estado = $input['estado'];

            if (!isset($id))          return $this->sendBadRequest('Parámetro ID requerido');
            if (!is_numeric($id))     return $this->sendBadRequest('Parámetro ID numérico');
            if ($id < 1)                return $this->sendBadRequest('Parámetro ID numérico mayor a 0');

            if (!isset($estado))          return $this->sendBadRequest('Parámetro Estado requerido');
            if (!is_numeric($estado))     return $this->sendBadRequest('Parámetro Estado numérico');
            if ($estado < 1)                return $this->sendBadRequest('Parámetro Estado numérico mayor a 0');

            $usrcambiaestado = 0;
            try {
                $token = $this->getTokenfromRequest();
                $usrcambiaestado = $token->idusr;
                $establecimiento_id = $token->establecimiento_id;
            } catch (Exception $e) {
                return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_UNAUTHORIZED);
            }

            $db = db_connect($this->getCadenaConexion());
            $SQL = 'update tbcliente set usrcambiaestado=' . $usrcambiaestado . ', estado=' . $estado . " where establecimiento_id=".$establecimiento_id." and id=" . $id;
            $db->query($SQL);
            if ($db->affectedRows() == 0)
                return $this->sendBadRequest('No se actualizó ningún cliente');
            else
                return $this->sendResponse(['message' => 'Se cambió el estado del cliente']);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->sendBadRequest("No tienes acceso a la base de datos");
            }
            return $this->sendResponse(['error' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
