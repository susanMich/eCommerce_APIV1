<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class UserModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey       = 'id';
    protected $allowedFields = ['usuario', 'clave','nombres','rol'];
    protected $updatedField = 'fechamodificacion';

    

    private function getUpdatedDataWithHashedPassword(array $data): array
    {
        if (isset($data['data']['clave'])) {
            $plaintextPassword = $data['data']['clave'];
            $data['data']['clave'] = $this->hashPassword($plaintextPassword);
        }
        return $data;
    }

    private function hashPassword(string $plaintextPassword): string
    {
        return password_hash($plaintextPassword, PASSWORD_BCRYPT);
    }
                                      
    public function findUserByIDFromJWTVal(string $id)
    {
        $user = $this->asArray()->where(['id' => $id])->first();
        if (!$user) //Si no encuentra correo, lanza exception
            throw new Exception('No existe usuario registrado: '.$id);
        return $user;
    }

    public function findUserByUsername(string $userName)
    {
        return $this->asArray()->where(['usuario' => $userName])->first();
    }
}