<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Model\Data;

use JonVaughan\WebapiClient\Api\Data\ApiObjectInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\AbstractSimpleObjectBuilder;

class ApiObject implements ApiObjectInterface
{

    private array $_data;

    public function __construct(array $data = [])
    {
        $this->_data = $data;
    }

    protected function _get($key)
    {
        return $this->_data[$key] ?? null;
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        return $this->_get('data');
    }

    /**
     * Set value for the given key
     *
     * @param array $value
     * @return $this
     */
    public function setData($data)
    {
        $this->_data['data'] = $data;
        return $this;
    }
}
