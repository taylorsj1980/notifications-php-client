<?php

namespace spec\integration\Alphagov\Notifications;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Alphagov\Notifications\Authentication\JWTAuthenticationInterface;
use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception\UnexpectedValueException;

use GuzzleHttp\Psr7\Uri;
use Http\Client\HttpClient as HttpClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Integration Tests for the PHP Notify Client.
 *
 *
 * Class ClientSpec
 * @package spec\Alphagov\Notifications
 */
class ClientSpec extends ObjectBehavior
{
    private static $notificationId;

    function let(){

        $this->beConstructedWith([
            'baseUrl'       => getenv('NOTIFY_API_URL'),
            'serviceId'     => getenv('SERVICE_ID'),
            'apiKey'        => getenv('API_KEY'),
            'httpClient'    => new \Http\Adapter\Guzzle6\Client
        ]);

    }

    function it_is_initializable(){
        $this->shouldHaveType('Alphagov\Notifications\Client');
    }

    function it_receives_the_expected_response_when_sending_an_email_notification(){

        $response = $this->sendEmail( getenv('FUNCTIONAL_TEST_EMAIL'), getenv('EMAIL_TEMPLATE_ID'), [
            "name" => "Foo"
        ]);

        $response->shouldBeArray();

        $response['data']->shouldBeArray();

        $response['data']->shouldHaveKey( 'notification' );
        $response['data']->shouldHaveKey( 'body' );
        $response['data']->shouldHaveKey( 'subject' );
        $response['data']->shouldHaveKey( 'template_version' );

        $response['data']['notification']->shouldBeArray();
        $response['data']['notification']->shouldHaveKey( 'id' );
        $response['data']['notification']['id']->shouldBeString();

        $response['data']['body']->shouldBeString();
        $response['data']['body']->shouldBe("Hello Foo\n\nFunctional test help make our world a better place");

        $response['data']['subject']->shouldBeString();
        $response['data']['subject']->shouldBe("Functional Tests are good");

        $response['data']['template_version']->shouldBeInteger();

        self::$notificationId = $response['data']['notification']['id']->getWrappedObject();

    }

    function it_receives_the_expected_response_when_looking_up_an_email_notification() {

      // Requires the 'it_receives_the_expected_response_when_sending_an_email_notification' test to have completed successfully
      if(is_null(self::$notificationId)) {
          throw new UnexpectedValueException('Notification ID not set');
      }

      $notificationId = self::$notificationId;

      // Retrieve email notification by id and verify contents
      $response = $this->getNotification($notificationId);

      $response->shouldHaveKey('data');
      $response['data']->shouldBeArray();

      $response['data']->shouldHaveKey( 'notification' );
      $response['data']['notification']->shouldBeArray();

      $response['data']['notification']->shouldHaveKey( 'id' );
      $response['data']['notification']['id']->shouldBeString();
      $response['data']['notification']['id']->shouldBeEqualTo($notificationId);

      $response['data']['notification']->shouldHaveKey( 'body' );
      $response['data']['notification']['body']->shouldBeString();
      $response['data']['notification']['body']->shouldBeEqualTo("Hello Foo\n\nFunctional test help make our world a better place");

      $response['data']['notification']->shouldHaveKey( 'status' );
      $response['data']['notification']['status']->shouldBeString();

      $response['data']['notification']->shouldHaveKey( 'notification_type' );
      $response['data']['notification']['notification_type']->shouldBeString();
      $response['data']['notification']['notification_type']->shouldBeEqualTo("email");

      $response['data']['notification']->shouldHaveKey( 'subject' );
      $response['data']['notification']['subject']->shouldBeString();
      $response['data']['notification']['subject']->shouldBeEqualTo("Functional Tests are good");

      $response['data']['notification']->shouldHaveKey( 'template_version' );
      $response['data']['notification']['template_version']->shouldBeInteger();

    }

    function it_receives_the_expected_response_when_sending_an_sms_notification(){

        $response = $this->sendSms( getenv('FUNCTIONAL_TEST_NUMBER'), getenv('SMS_TEMPLATE_ID'), [
            "name" => "Foo"
        ]);

        $response->shouldBeArray();

        $response['data']->shouldBeArray();

        $response['data']->shouldHaveKey( 'notification' );
        $response['data']->shouldHaveKey( 'body' );
        $response['data']->shouldNotHaveKey( 'subject' );
        $response['data']->shouldHaveKey( 'template_version' );

        $response['data']['notification']->shouldBeArray();
        $response['data']['notification']->shouldHaveKey( 'id' );
        $response['data']['notification']['id']->shouldBeString();

        $response['data']['body']->shouldBeString();
        $response['data']['body']->shouldBe("Hello Foo\n\nFunctional Tests make our world a better place");

        $response['data']['template_version']->shouldBeInteger();

        self::$notificationId = $response['data']['notification']['id']->getWrappedObject();

    }

    function it_receives_the_expected_response_when_looking_up_an_sms_notification() {

      // Requires the 'it_receives_the_expected_response_when_sending_an_sms_notification' test to have completed successfully
      if(is_null(self::$notificationId)) {
          throw new UnexpectedValueException('Notification ID not set');
      }

      $notificationId = self::$notificationId;

      // Retrieve sms notification by id and verify contents
      $response = $this->getNotification($notificationId);

      $response->shouldHaveKey('data');
      $response['data']->shouldBeArray();

      $response['data']->shouldHaveKey( 'notification' );
      $response['data']['notification']->shouldBeArray();

      $response['data']['notification']->shouldHaveKey( 'id' );
      $response['data']['notification']['id']->shouldBeString();
      $response['data']['notification']['id']->shouldBeEqualTo($notificationId);

      $response['data']['notification']->shouldHaveKey( 'body' );
      $response['data']['notification']['body']->shouldBeString();
      $response['data']['notification']['body']->shouldBeEqualTo("Hello Foo\n\nFunctional Tests make our world a better place");

      $response['data']['notification']->shouldHaveKey( 'status' );
      $response['data']['notification']['status']->shouldBeString();

      $response['data']['notification']->shouldHaveKey( 'notification_type' );
      $response['data']['notification']['notification_type']->shouldBeString();
      $response['data']['notification']['notification_type']->shouldBeEqualTo("sms");

      $response['data']['notification']->shouldNotHaveKey( 'subject' );

      $response['data']['notification']->shouldHaveKey( 'template_version' );
      $response['data']['notification']['template_version']->shouldBeInteger();

    }

    function it_receives_the_expected_response_when_looking_up_all_notifications() {

      // Retrieve all notifications and verify each is correct (email & sms)
      $response = $this->listNotifications();

      $response->shouldHaveKey('links');
      $response->shouldHaveKey('page_size');
      $response->shouldHaveKey('notifications');
      $response->shouldHaveKey('total');

      $response['links']->shouldBeArray();
      $response['page_size']->shouldBeInteger();
      $response['notifications']->shouldBeArray();
      $response['total']->shouldBeInteger();

      $notifications = $response['notifications'];
      $total_notifications_count = count($notifications->getWrappedObject());

      for( $i = 0; $i < $total_notifications_count; $i++ ) {

          $notification = $notifications[$i];

          $notification->shouldBeArray();

          $notification->shouldHaveKey( 'notification_type' );
          $notification['notification_type']->shouldBeString();
          $notification_type = $notification['notification_type']->getWrappedObject();

          $notification->shouldHaveKey( 'id' );
          $notification['id']->shouldBeString();

          $notification->shouldHaveKey( 'body' );
          $notification['body']->shouldBeString();

          $notification->shouldHaveKey( 'status' );
          $notification['status']->shouldBeString();

          $notification->shouldHaveKey( 'template_version' );
          $notification['template_version']->shouldBeInteger();

          if ( $notification_type == "sms" ) {

            $notification->shouldNotHaveKey( 'subject' );

          } elseif ( $notification_type == "email") {

            $notification->shouldHaveKey( 'subject' );
            $notification['subject']->shouldBeString();
            $notification['subject']->shouldBeEqualTo("Functional Tests are good");

          }
      }

    }

}
