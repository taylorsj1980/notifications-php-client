<?php
namespace spec\integration\Alphagov\Notifications;

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
 *
 * Class ClientSpec
 * @package spec\Alphagov\Notifications
 */
class ClientSpec extends ObjectBehavior
{

    function let(){

        $this->beConstructedWith([
            'serviceId'     => '{your service id}',
            'apiKey'        => '{your api key}',
            'httpClient'    => new \Http\Adapter\Guzzle6\Client
        ]);

    }

    function it_is_initializable(){
        $this->shouldHaveType('Alphagov\Notifications\Client');
    }

}
