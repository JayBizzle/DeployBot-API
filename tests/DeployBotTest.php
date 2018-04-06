<?php

namespace DeployBot\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Jaybizzle\DeployBot;
use PHPUnit\Framework\TestCase;

class DeployBotTest extends TestCase
{
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

        $db = $this->getMockBuilder(DeployBot::class)
                    ->setConstructorArgs(['foo_api_key', 'bar_account_name'])
                    ->disableOriginalConstructor()
                    ->setMethods(null)
                    ->getMock();

        $result = $db->parseApiEndpoint('foobar');

        $this->assertEquals($expected, $result);
    }

    public function testGetUsersResponse()
    {
        $mockResponse = file_get_contents(__DIR__.'/responses/getUsersResponse.txt');
        $mock = new MockHandler([
            \GuzzleHttp\Psr7\parse_response($mockResponse),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $db = new DeployBot('foo_api_key', 'bar_account_name', $client);

        $result = $db->getUsers();

        $this->assertEquals(3, count($result->entries));
    }

    public function testAddingQueryParams()
    {
        $args = [2];

        $db = new DeployBot('foo_api_key', 'bar_account_name', new \stdClass());

        $db->addQuery('limit', $args);

        $this->assertTrue(array_key_exists('limit', $db->query));
        $this->assertEquals(2, $db->query['limit']);
    }

    public function providerParamNames()
    {
        return [
            ['fooBar', 'foo_bar'],
            ['FooBar', 'foo_bar'],
            ['FOOBAR', 'f_o_o_b_a_r'],
        ];
    }
}
