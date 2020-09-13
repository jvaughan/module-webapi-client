<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Model;

use JonVaughan\WebapiClient\Api\ApiObjectRepositoryInterface;
use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterface;
use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterfaceFactory;
use JonVaughan\WebapiClient\Api\Data\ApiObjectInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use GuzzleHttp\Client;

class ApiObjectRepository implements ApiObjectRepositoryInterface
{
    private Client $httpClient;
    private string $uri;
    private string $bearerToken;

    private ApiObjectSearchResultsInterfaceFactory $apiObjectSearchResultsFactory;
    private ApiObjectInterfaceFactory $apiObjectFactory;
    private JsonSerializer $jsonSerializer;

    public function __construct(
        Client $httpClient,
        ApiObjectSearchResultsInterfaceFactory $apiObjectSearchResultsFactory,
        ApiObjectInterfaceFactory $apiObjectFactory,
        JsonSerializer $jsonSerializer,
        $uri = '',
        $bearerToken = ''
    )
    {
        $this->httpClient = $httpClient;
        $this->uri = $uri;
        $this->bearerToken = $bearerToken;
        $this->apiObjectSearchResultsFactory = $apiObjectSearchResultsFactory;
        $this->apiObjectFactory = $apiObjectFactory;
        $this->jsonSerializer = $jsonSerializer;
//        $this->apiObjectSearchResultsFactory->create();
    }

    /**
     * @return ApiObjectSearchResultsInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getList()
    {
        $items = [];
        $response = $this->httpClient->request(
            'GET',
            $this->uri,
            [
                'headers' => ['Authorization' => 'Bearer ' . $this->bearerToken]
            ]
        );
        $body = $this->jsonSerializer->unserialize((string) $response->getBody());

        if (isset($body['items']) && count($body['items'])) {
            foreach ($body['items'] as $responseItem) {
                $apiObject = $this->apiObjectFactory->create();
                $apiObject->setData($responseItem);
                $items[] = $apiObject;
            }
        }
        else {
            $apiObject = $this->apiObjectFactory->create();
            $apiObject->setData(
                $this->jsonSerializer->unserialize((string)$response->getBody())
            );
            $items [] = $apiObject;
        }

        $searchResults = $this->apiObjectSearchResultsFactory->create();
        $searchResults->setItems($items);
        return $searchResults;
    }
}
