<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Test\Integration\Model\Api;

use JonVaughan\WebapiClient\Api\Data\ApiObjectInterface;
use JonVaughan\WebapiClient\Api\WebapiClientServiceInterface;
use JonVaughan\WebapiClient\Api\WebapiClientServiceInterfaceFactory;
use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterface;
use JonVaughan\WebapiClient\Model\WebapiClientService;

use Magento\Framework\Api\SearchCriteriaInterface;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class WebapiClientServicePutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WebapiClientService
     */
    private $webapiClientService;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ApiObjectInterface
     */
    private $apiObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->apiObject = $this->objectManager->create(ApiObjectInterface::class);
    }

    public function testClientServiceFactoryReturnsClientService(): void
    {
        $this->assertInstanceOf(
            WebapiClientServiceInterface::class,
            $this->getWebapiClientService(
                $this->getMockClient()
            )
        );
    }

    public function testMakesOneRequest(): void
    {
        $container = [];
        $client = $this->getMockClient($container);

        $this->getWebapiClientService($client)
            ->put($this->apiObject);

        $this->assertCount(
            1,
            $container,
            'One client request should be made'
        );
    }

    public function testPutsCorrectUrl(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"apikey": "apivalue"}'),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client([
            'handler' =>  $handlerStack
        ]);

        $this->getWebapiClientService($client, 'https://example.com/api/correct-endpoint')
            ->put($this->apiObject);

        $transaction = $container[0];
        /**
         * @var Request $request
         */
        $request = $transaction['request'];

        $this->assertSame(
            'PUT',
            $request->getMethod(),
            'Method should be PUT'
        );
        $this->assertSame(
            'https',
            $request->getUri()->getScheme(),
            'Scheme should be https'
        );
        $this->assertSame(
            'example.com',
            $request->getUri()->getHost(),
            'Host should be example.com'
        );
        $this->assertSame(
            '/api/correct-endpoint',
            $request->getUri()->getPath(),
            'Path should be /api/correct-endpoint'
        );
    }

    public function testRequestBodyHasCorrectJson(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"apikey": "apivalue"}'),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client([
            'handler' =>  $handlerStack
        ]);

        $this->apiObject->setData([
            'key1'  => 'value1',
        ]);

        $this->getWebapiClientService($client, 'https://example.com/api/correct-endpoint')
            ->put($this->apiObject);

        $transaction = $container[0];
        /**
         * @var Request $request
         */
        $request = $transaction['request'];

        $this->assertJson(
            (string) $request->getBody(),
            'body is not valid JSON'
        );

        $expectedJson = <<<EOT
{"key1": "value1"}
EOT;
        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            (string) $request->getBody()
        );
    }

    public function testHasAuthorizationBearerToken(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"apikey": "apivalue"}'),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client([
            'handler' =>  $handlerStack
        ]);

        $this->getWebapiClientService($client, 'https://example.com/api/correct-endpoint', 'test-token')
            ->put($this->apiObject);

        $transaction = $container[0];
        /**
         * @var Request $request
         */
        $request = $transaction['request'];

        $this->assertSame(
            'Bearer test-token',
            $request->getHeaderLine('Authorization'),
            'Bearer token value is incorrect'
        );
    }

    /**
     * @param Client $client
     * @return WebapiClientService|object
     * @var string $bearerToken
     * @var string $uri
     */
    private function getWebapiClientService(
        Client $client,
        $uri = 'https://example.com/api/endpoint',
        $bearerToken = ''
    ) {
        $factory = new WebapiClientServiceInterfaceFactory($this->objectManager);
        return $factory->create(
            [
                'httpClient'    => $client,
                'uri'           => $uri,
                'bearerToken'   => $bearerToken
            ]
        );
    }

    /**
     * @param array $container
     * @param null $mockHandler
     * @return Client
     */
    private function getMockClient(&$container = [], $mockHandler = null): Client
    {
        if (is_null($mockHandler)) {
            $mockHandler = new MockHandler([
                new Response(200, ['X-Foo' => 'Bar'], '{"apikey": "apivalue"}'),
            ]);
        }

        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);
        return new Client([
            'handler' =>  $handlerStack
        ]);
    }
}
