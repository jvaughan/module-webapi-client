<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Test\Integration\Model\Api;

use JonVaughan\WebapiClient\Api\ApiObjectRepositoryInterface;
use JonVaughan\WebapiClient\Api\ApiObjectRepositoryInterfaceFactory;
use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterface;
use JonVaughan\WebapiClient\Model\ApiObjectRepository;

use Magento\Framework\Api\SearchCriteriaInterface;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class ApiObjectRepositoryGetListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ApiObjectRepository
     */
    private $apiObjectRepository;

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
            ApiObjectRepositoryInterface::class,
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
            ->getList();

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
        $items = $this->getApiObjectRepository($client)->getList();
    }

    public function testReturnsSearchResults(): void
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], '{"apikey": "apivalue"}'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client([
            'handler' =>  $handlerStack
        ]);

        $result = $this->getApiObjectRepository($client)->getList();

        $this->assertInstanceOf(
            ApiObjectSearchResultsInterface::class,
            $result
        );
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

    public function testReturnsMultipleItems(): void
    {
        $jsonResponse = <<<EOT
{
    "items": [
        {
            "item1_key1": "item1 key1 value",
            "item1_key2": "item1 key2 value"
        },
        {
            "item2_key1": "item2 key1 value",
            "item2_key2": "item2 key2 value"
        }
    ]
}
EOT;
        $mockHandler = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], $jsonResponse),
        ]);

        $container = [];
        $client = $this->getMockClient($container, $mockHandler);

        $items = $this->getApiObjectRepository($client)
            ->getList()
            ->getItems();

        $this->assertSame(
            [
                'item1_key1'   => 'item1 key1 value',
                'item1_key2'   => 'item1 key2 value',
            ],
            $items[0]->getData()
        );
        $this->assertSame(
            [
                'item2_key1'   => 'item2 key1 value',
                'item2_key2'   => 'item2 key2 value',
            ],
            $items[1]->getData()
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
     * @var string $uri
     * @var string $bearerToken
     * @return ApiObjectRepository|object
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
