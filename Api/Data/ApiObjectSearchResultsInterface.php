<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Api\Data;

use JonVaughan\WebapiClient\Api\Data\ApiObjectInterface;

interface ApiObjectSearchResultsInterface
{
    /**
     * Get Api Object list.
     * @return ApiObjectInterface[]
     */
    public function getItems();

    /**
     * Set Api Object list.
     * @param ApiObjectInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
