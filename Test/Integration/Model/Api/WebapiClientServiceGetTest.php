<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Test\Integration\Model\Api;

use JonVaughan\WebapiClient\Api\WebapiClientServiceInterface;
use JonVaughan\WebapiClient\Api\ApiObjectRepositoryInterfaceFactory;
use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterface;
use JonVaughan\WebapiClient\Model\WebapiClientService;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class WebapiClientServiceGetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testRepositoryInterfaceFactoryReturnsInterface(): void
    {
        $this->assertInstanceOf(
            WebapiClientServiceInterface::class,
            $this->getApiObjectRepository(
                $this->getMockClient()
            )
        );
    }

    public function testMakesOneRequest(): void
    {
        $container = [];
        $client = $this->getMockClient($container);

        $this->getApiObjectRepository($client)->getList();

        $this->assertCount(
            1,
            $container,
            'One client request should be made'
        );
    }

    public function testGetsCorrectUrl(): void
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

        $this->getApiObjectRepository($client, 'https://example.com/api/correct-endpoint')
            ->get();

        $transaction = $container[0];
        /**
         * @var Request $request
         */
        $request = $transaction['request'];

        $this->assertSame(
            'GET',
            $request->getMethod(),
            'Method should be GET'
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

    public function testCanReturnOneAoInterface(): void
    {
        $client = $this->getMockClient();
        $items = $this->getApiObjectRepository($client)->get();
    }

    public function testCanReturnOneApiObjectWithData(): void
    {
        $client = $this->getMockClient();

        $result = $this->getApiObjectRepository($client)->getList();
        $this->assertCount(1, $result->getItems());

        $items = $result->getItems();
        $item = $items[0];
        $this->assertInstanceOf(
            \JonVaughan\WebapiClient\Api\Data\ApiObjectInterface::class,
            $item
        );

        $this->assertSame(
            ['apikey'  => 'apivalue'],
            $item->getData()
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

        $this->getApiObjectRepository($client, 'https://example.com/api/correct-endpoint', 'test-token')
            ->getList();

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
     *@var string $bearerToken
     * @var string $uri
     */
    private function getApiObjectRepository(
        Client $client,
        $uri = 'https://example.com/api/endpoint',
        $bearerToken = ''
    ) {
        $factory = new ApiObjectRepositoryInterfaceFactory($this->objectManager);
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
