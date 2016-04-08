<?php
namespace Alphagov\Notifications\Authentication;

/**
 * Interface representing a GOV.UK Notify compatible JSON Web Token generator.
 *
 * Interface JWTAuthenticationInterface
 * @package Alphagov\Notifications\Authentication
 */
interface JWTAuthenticationInterface {

    /**
     * Generate a JSON Web Token.
     *
     * @param string        $request The pre-encoded request
     * @param string|null   $payload The pre-encoded payload
     *
     * @return string The generated token
     */
    public function createToken( $request, $payload = null );

}