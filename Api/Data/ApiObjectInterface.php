<?php declare(strict_types=1);

namespace JonVaughan\WebapiClient\Api\Data;

interface ApiObjectInterface
{
    /**
     * @return array|null
     */
    public function getData();

    /**
     * @param array $data
     */
    public function setData(array $data);
}
