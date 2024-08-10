<?php

use App\Models\UserModel;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getTokenFromRequest($authenticationHeader): string
{
    if (is_null($authenticationHeader)) { //JWT is absent
        throw new Exception('Se requiere Token Válido');
    }
    //JWT is sent from client in the format Bearer XXXXXXXXX
    return explode(' ', $authenticationHeader)[1];
}

function validateToken(string $encodedToken)
{
    $key = config('App')->JWTSecret;
    try {

        $decodedToken = JWT::decode($encodedToken, new Key($key, 'HS256'));
        $userModel = new UserModel();  //Si no existe envia una excepción el Modelo
        $userModel->findUserByIDFromJWTVal($decodedToken->idusr);
        return $decodedToken;

    } catch (ExpiredException $e) {
        throw new Exception('Token ha expirado');
    } catch (SignatureInvalidException $e) {
        throw new Exception('Firma del token inválida'); 
    } catch (Exception $e) { 
        throw new Exception($e->getMessage());
    }
}

function genToken(string $username, int $idusr)
{
    $key = config('App')->JWTSecret;
    $tokenTimeToLive = config('App')->tokenTimeToLive;
    $payload = [
        'idusr' => $idusr,
        'usuario' => $username,
        'iat' => time(),
        'exp' => time() + $tokenTimeToLive,
    ];
    $jwt = JWT::encode($payload, $key, 'HS256');
    return $jwt;
}


