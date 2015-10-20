<?php

namespace RedCode\Common\Cache;

use Doctrine\Common\Cache\CacheProvider;

class AerospikeCache extends CacheProvider
{
    const DEFAULT_SET_NAME = 'custom_cache';
    const WRAPPER_NAME = 'data';

    /**
     * @var \Aerospike
     */
    private $aerospike;

    /**
     * @var string
     */
    private $setName;

    /**
     * @param \Aerospike $aerospike
     *
     * @return self
     */
    public function __construct(\Aerospike $aerospike)
    {
        $this->aerospike = $aerospike;
        $this->setSetName(self::DEFAULT_SET_NAME);
    }

    /**
     * @param string $setName
     *
     * @return self
     */
    public function setSetName($setName)
    {
        $this->setName = $setName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        $record = null;
        $aeroKey = $this->getAerospikeKey($id);
        $status = $this->aerospike->get($aeroKey, $record);
        $record = isset($record['bins'][self::WRAPPER_NAME]) ? $record['bins'][self::WRAPPER_NAME] : false;

        switch ($status) {
            case \Aerospike::OK:
                return $record;
            case \Aerospike::ERR_RECORD_NOT_FOUND:
                return false;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return $this->doFetch($id) !== false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $aeroKey = $this->getAerospikeKey($id);
        $data = [self::WRAPPER_NAME => $this->prepareValue($data)];
        $ttl = !empty($lifeTime) ? $lifeTime : $this->getTimeLimitFromNamespace($aeroKey);
        $status = $this->aerospike->put(
            $aeroKey,
            $data,
            $ttl,
            [
                \Aerospike::OPT_POLICY_EXISTS => \Aerospike::POLICY_EXISTS_CREATE_OR_REPLACE,
            ]
        );

        return $status === \Aerospike::OK;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        $status = $this->aerospike->remove(
            $this->getAerospikeKey($id),
            [\Aerospike::OPT_POLICY_RETRY => \Aerospike::POLICY_RETRY_NONE]
        );

        return $status === \Aerospike::OK;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        return;
    }

    /**
     * Create and return Aerospike internal key.
     *
     * @param string $id
     *
     * @return array
     */
    private function getAerospikeKey($id)
    {
        return $this->aerospike->initKey(
            $this->getNamespace(),
            $this->setName,
            $id
        );
    }

    /**
     * Prepare data before save.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function prepareValue($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->prepareValue($value);
            }
        }
        if (is_null($data)) {
            return false;
        }

        return $data;
    }

    /**
     * Get pre-configured ttl from namespace.
     *
     * @param array $aeroKey
     *
     * @return int
     */
    private function getTimeLimitFromNamespace(array $aeroKey)
    {
        $metadata = null;
        if ($this->aerospike->exists($aeroKey, $metadata) == \Aerospike::OK) {
            return (int) $metadata['ttl'];
        }

        return 0;
    }
}
