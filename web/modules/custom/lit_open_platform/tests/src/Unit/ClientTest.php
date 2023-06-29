<?php

namespace Drupal\Tests\lit_open_platform\Unit;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lit_open_platform\Api\Client;

/**
 * Unit tests for the open platform client.
 *
 * @group lit
 *
 * @coversDefaultClass \Drupal\lit_open_platform\Api\Client
 */
class ClientTest extends KernelTestBase {

  /**
   * A client id.
   *
   * @const string
   */
  protected const CLIENT_ID = 'client_id';

  /**
   * The client secret.
   *
   * @const string
   */
  protected const CLIENT_SECRET = 'client_secret';

  /**
   * The API client.
   *
   * @var \Drupal\lit_open_platform\Api\Client
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->client = Client::getInstance(self::CLIENT_ID, self::CLIENT_SECRET);
  }

  /**
   * Test verifying token.
   *
   * @covers ::verifyToken
   */
  public function testVerifyToken() {
    $token = [
      'token_type' => 'bearer',
      'access_token' => '0c31a9f1df69562215eb0d61791ec41e9992a359',
      'expires_in' => 2592000,
    ];

    $this->assertTrue($this->client->verifyToken($token));
  }

  /**
   * Test getting basic token.
   *
   * @covers ::getBasicToken
   */
  public function testGetBasicToken() {
    $base_token = base64_encode(self::CLIENT_ID . ':' . self::CLIENT_SECRET);

    $this->assertEquals($base_token, $this->client->getBasicToken());
  }

  /**
   * Test building url.
   *
   * @covers ::buildUrl
   */
  public function testBuildUrl() {
    $url = 'https://openplatform.dbc.dk/v2/test';

    // Get a reflected, accessible version of the protected ::buildUrl() method.
    $createBuildUrlMethod = $this->getAccessibleMethod(Client::class, 'buildUrl');

    $this->assertEquals($url, $createBuildUrlMethod->invokeArgs($this->client, ['test']));
    $this->assertEquals($url, $createBuildUrlMethod->invokeArgs($this->client, ['/test']));
  }

  /**
   * Get an accessible method using reflection.
   */
  public function getAccessibleMethod($class_name, $method_name) {
    $class = new \ReflectionClass($class_name);
    $method = $class->getMethod($method_name);
    $method->setAccessible(TRUE);
    return $method;
  }

}
