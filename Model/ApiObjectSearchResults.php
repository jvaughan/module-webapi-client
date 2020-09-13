<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Model;

use JonVaughan\WebapiClient\Api\Data\ApiObjectSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ApiObjectSearchResults extends SearchResults implements ApiObjectSearchResultsInterface
{
}
