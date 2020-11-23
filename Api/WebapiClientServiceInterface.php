<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Api;

use JonVaughan\WebapiClient\Api\Data\ApiObjectInterface;
use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterface;

interface WebapiClientServiceInterface
{
    /**
     * @return ApiObjectInterface
     */
    public function get();

    /**
     * Retrieve Config matching the specified criteria.
     * @return ApiObjectSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList();

    /**
     * @param ApiObjectInterface $apiObject
     * @return ApiObjectInterface
     */
    public function put(
        ApiObjectInterface $apiObject
    );

    /**
     * @param array $formParams
     * @return ApiObjectInterface
     */
    public function postForm(
        array $formParams
    );
}
