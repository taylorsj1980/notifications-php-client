<?php
namespace spec\Alphagov\Notifications;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Alphagov\Notifications\Authentication\JWTAuthenticationInterface;
use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception as NotifyException;

use GuzzleHttp\Psr7\Uri;
use Http\Client\HttpClient as HttpClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Tests for the PHP Notify Client.
 *
 * Note: The Client never make assumptions about the response of successful queries to the Notify API.
 *          Therefore the response we test against here *may* differ from that the real API returns.
 *          That's okay, as testing response schema/data is beyond our scope.
 *
 * Class ClientSpec
 * @package spec\Alphagov\Notifications
 */
class ClientSpec extends ObjectBehavior
{
    const BASE_URL = 'https://api-test';
    const TEST_JWT_TOKEN = 'jwt-token';

    private $httpClient;

    function getConstructorOptions( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        $this->httpClient = $httpClient;

        $authenticator->createToken()->willReturn( self::TEST_JWT_TOKEN );

        $options = [
            'baseUrl' => self::BASE_URL,
            'authenticator' => $authenticator,
            'httpClient' => $this->httpClient,
        ];

        return $options;
    }


    function let( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        $this->beConstructedWith( $this->getConstructorOptions( $httpClient, $authenticator ) );

    }

    function it_is_initializable(){
        $this->shouldHaveType('Alphagov\Notifications\Client');
    }

    //----------------------------------------------------------------------------------------------------------
    // Test constructor variations

    function it_exceptions_without_a_http_client( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['httpClient'] );

        //---------------------------------
        // Perform action & check result

        $this->beConstructedWith( $options );
        $this->shouldThrow('Alphagov\Notifications\Exception\InvalidArgumentException')->duringInstantiation();

    }

    function it_exceptions_without_an_authenticator( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['authenticator'] );

        //---------------------------------
        // Perform action & check result

        $this->beConstructedWith( $options );
        $this->shouldThrow('Alphagov\Notifications\Exception\InvalidArgumentException')->duringInstantiation();

    }

    function it_works_with_a_service_id_and_api_key( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['authenticator'] );

        $options += [
            'serviceId' => '1546058f-5a25-4334-85ae-e68f2a44bbaf',
            'apiKey'    => '522ec739-ca63-3ec5-b082-08ce08ad65e2',
        ];

        //---------------------------------
        // Perform action

        /*
         * The below will throw an exception if a valid authenticator was not created.
         */

        $this->beConstructedWith( $options );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode([])
            )
        );

        $this->listNotifications();

    }

    //----------------------------------------------------------------------------------------------------------
    // Lookups (GETs) with expected success

    function it_generates_the_expected_request_when_looking_up_notification(){

        //---------------------------------
        // Test Setup

        $id = '35836a9e-5a97-4d99-8309-0c5a2c3dbc72';

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $this->getNotification( $id );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($id) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . sprintf( Client::PATH_NOTIFICATION_LOOKUP, $id ) ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_looking_up_notification(){

        //---------------------------------
        // Test Setup

        $id = '35836a9e-5a97-4d99-8309-0c5a2c3dbc72';

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->getNotification( $id );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }

    function it_generates_the_expected_request_when_listing_notifications(){

        //---------------------------------
        // Test Setup

        $filters = ['status'=>'delivered', 'page'=>'1', 'template_type'=> 'sms'];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notifications' => array()])
            )
        );

        //---------------------------------
        // Perform action

        $this->listNotifications( $filters );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($filters) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            //---

            $url = new Uri( self::BASE_URL . Client::PATH_NOTIFICATION_LIST );

            foreach( $filters as $name => $value ){
                $url = URI::withQueryValue($url, $name, $value );
            }

            // With the correct URL
            if( $v->getUri() != $url ){
                return false;
            }

            //---

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_listing_notifications(){

        //---------------------------------
        // Test Setup

        $data = [[
            'created_at'=> '2016-04-06T11:06:10.260722+00:00',
            'id' => '217ce465-d16a-4179-928d-c1a73eb3f377'
        ]];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notifications' => $data])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->listNotifications();

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notifications', $data);

    }

    //----------------------------------------------------------------------------------------------------------
    // Sending (POSTs) with expected success

    function it_generates_the_expected_request_when_sending_sms(){

        //---------------------------------
        // Test Setup

        $payload = [
            'to' => '+447834000000',
            'template'=> 118,
            'personalisation' => [
                'name'=>'Fred'
            ]
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                [ 'Content-type'  => 'application/json' ],
                json_encode([ 'notification_id' => 'xxx' ])
            )
        );

        //---------------------------------
        // Perform action

        $this->sendSms( $payload['to'], $payload['template'], $payload['personalisation'] );

        //---------------------------------
        // Check result

        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($payload) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . Client::PATH_NOTIFICATION_SEND_SMS ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            // With the expected body.
            if( json_decode( $v->getBody(), true ) != $payload ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_sending_sms(){

        //---------------------------------
        // Test Setup

        $id = '35836a9e-5a97-4d99-8309-0c5a2c3dbc72';

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->sendSms( '+447834000000', 118, [ 'name'=>'Fred' ] );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }

    function it_generates_the_expected_request_when_sending_email(){

        //---------------------------------
        // Test Setup

        $payload = [
            'to' => 'text@example.com',
            'template'=> 118,
            'personalisation' => [
                'name'=>'Fred'
            ]
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                [ 'Content-type'  => 'application/json' ],
                json_encode([ 'notification_id' => 'xxx' ])
            )
        );

        //---------------------------------
        // Perform action

        $this->sendEmail( $payload['to'], $payload['template'], $payload['personalisation'] );

        //---------------------------------
        // Check result

        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($payload) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . Client::PATH_NOTIFICATION_SEND_EMAIL ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            // With the expected body.
            if( json_decode( $v->getBody(), true ) != $payload ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_sending_email(){

        //---------------------------------
        // Test Setup

        $id = '35836a9e-5a97-4d99-8309-0c5a2c3dbc72';

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->sendEmail( 'text@example.com', 118, [ 'name'=>'Fred' ] );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }


    //----------------------------------------------------------------------------------------------------------
    // Actions with expected errors

    function it_receives_null_when_the_api_returns_404(){

        //---------------------------------
        // Test Setup

        $code = 404;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                $code,
                ['Content-type'  => 'application/json']
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->getNotification( '35836a9e-5a97-4d99-8309-0c5a2c3dbc72' );

        //---------------------------------
        // Check result

        $response->shouldBeNull();

    }

    function it_receives_an_exception_when_the_api_returns_500(){

        //---------------------------------
        // Test Setup

        $code = 500;
        $error = 'Error Reason';
        $response = new Response(
            $code,
            ['Content-type'  => 'application/json'],
            json_encode(['message' => $error])
        );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            $response
        );

        //---------------------------------
        // Perform action & check result

        $this->shouldThrow(
            new NotifyException\ApiException( "HTTP:{$code} - {$error}", $code, $response )
        )->duringSendSms( '+447834000000', 118, [ 'name'=>'Fred' ] );

    }

}
