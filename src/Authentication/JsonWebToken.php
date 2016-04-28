<?php
namespace Alphagov\Notifications\Authentication;

use Firebase\JWT\JWT;

/**
 * Class for generating JSON Web Token compatible with GOV.UK Notify.
 *
 * Makes use of PHP-JWT: https://github.com/firebase/php-jwt
 *
 * Class JsonWebToken
 * @package Alphagov\Notifications\Authentication
 */
class JsonWebToken implements JWTAuthenticationInterface {

    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @var string
     */
    protected $key;


    /**
     * Instantiates a new JSON Web Token object.
     *
     * @param string $serviceId
     * @param string $key
     */
    public function __construct( $serviceId, $key ){

        $this->serviceId = $serviceId;
        $this->key = $key;

    }

    /**
     * Generate a JSON Web Token.
     *
     * @return string The generated token
     */
    public function createToken(){

        $claims = $this->generateClaims();

        return JWT::encode( $claims, $this->key );

    }

    /**
     * Prepare the required Notify claims.
     *
     * @return array
     */
    protected function generateClaims(){

        $claims = array(
            "iss" => $this->serviceId,
            "iat" => time(),
        );

        return $claims;

    }

}