<?php

namespace DeployBot\Test;

use Jaybizzle\DeployBot;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;

class DeployBotTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //
    }

    /**
     * @dataProvider providerParamNames
     */
    public function testCamelCaseMethodsReturnSnakeCaseParams($original, $expected)
    {
        $db = new DeployBot('foo_api_key', 'bar_account_name', new \stdClass());

        $result = $db->snakeCase($original);

        $this->assertEquals($expected, $result);
    }

    public function testParseApiEndpoint()
    {
        $expected = 'https://foobar.deploybot.com/api/v1/';

        $db = $this->getMockBuilder('Jaybizzle\DeployBot')
                    ->setConstructorArgs(array('foo_api_key', 'bar_account_name'))
                    ->disableOriginalConstructor()
                    ->setMethods(null)
                    ->getMock();

        $result = $db->parseApiEndpoint('foobar');

        $this->assertEquals($expected, $result);
    }

    public function testGetUsersResponse()
    {
        $client = new Client();

        $mock = new Mock();
        $mock->addResponse(__DIR__.'/responses/getUsersResponse.txt');

        $client->getEmitter()->attach($mock);

        $db = new DeployBot('foo_api_key', 'bar_account_name', $client);

        $result = $db->getUsers();

        $this->assertEquals(3, count($result->entries));
    }

    public function testAddingQueryParams()
    {
        $args = array(2);

        $db = new DeployBot('foo_api_key', 'bar_account_name', new \stdClass());

        $db->addQuery('limit', $args);

        $this->assertTrue(array_key_exists('limit', $db->query));
        $this->assertEquals(2, $db->query['limit']);
    }

    public function providerParamNames()
    {
        return array(
            array('fooBar', 'foo_bar'),
            array('FooBar', 'foo_bar'),
            array('FOOBAR', 'f_o_o_b_a_r'),
        );
    }

    public function tearDown()
    {

    }
}