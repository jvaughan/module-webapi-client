<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Api;

use JonVaughan\WebapiClient\Api\Data\ApiObjectInterface;
use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterface;

interface ApiObjectRepositoryInterface
{

//    /**
//     * Save Config
//     * @param ApiObjectInterface $apiObject
//     * @return ApiObjectInterface
//     * @throws \Magento\Framework\Exception\LocalizedException
//     */
//    public function save(
//        ApiObjectInterface $apiObject
//    );
//
//    /**
//     * Retrieve Config
//     * @param string $configId
//     * @return \Nanopore\StagingConfig\Api\Data\ConfigInterface
//     * @throws \Magento\Framework\Exception\LocalizedException
//     */
//    public function get($configId);

    /**
     * Retrieve Config matching the specified criteria.
     * @return ApiObjectSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList();


//    /**
//     * Delete Config
//     * @param \Nanopore\StagingConfig\Api\Data\ConfigInterface $config
//     * @return bool true on success
//     * @throws \Magento\Framework\Exception\LocalizedException
//     */
//    public function delete(
//        \Nanopore\StagingConfig\Api\Data\ConfigInterface $config
//    );
//
//    /**
//     * Delete Config by ID
//     * @param string $configId
//     * @return bool true on success
//     * @throws \Magento\Framework\Exception\NoSuchEntityException
//     * @throws \Magento\Framework\Exception\LocalizedException
//     */
//    public function deleteById($configId);
}
