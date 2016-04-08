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
     * @param string        $request The pre-encoded request
     * @param string|null   $payload The pre-encoded payload
     *
     * @return string The generated token
     */
    public function createToken( $request, $payload = null ){

        $claims = $this->generateClaims( $request, $payload );

        return JWT::encode( $claims, $this->key );

    }

    /**
     * Prepare the required Notify claims.
     *
     * @param $request
     * @param $payload
     *
     * @return array
     */
    protected function generateClaims( $request, $payload ){

        $claims = array(
            "iss" => $this->serviceId,
            "iat" => time(),
            "req" => $this->generateSignature( $request ),
        );

        if( is_string($payload) ){
            $claims['pay'] = $this->generateSignature( $payload );
        }

        return $claims;

    }

    /**
     * Return the signature for the passed data.
     *
     * @param string $data
     *
     * @return string
     */
    protected function generateSignature( $data ){

        return base64_encode(
            hash_hmac( 'sha256', $data, $this->key, true)
        );

    }

}