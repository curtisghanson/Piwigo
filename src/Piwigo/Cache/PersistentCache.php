<?php
namespace Piwigo\Cache;

/**
 * Provides a persistent cache mechanism across multiple page loads/sessions etc...
 */
abstract class PersistentCache
{
    private   $lifetime    = 86400;
    protected $instanceKey = PHPWG_VERSION;

    /**
     * @return a key that can be safely be used with get/set methods
     */
    public function makeKey($key)
    {
        if (is_array($key))
        {
            $key = implode('&', $key);
        }

        $key .= $this->instanceKey;

        return md5($key);
    }

    /**
     * Searches for a key in the persistent cache and fills corresponding value.
     * @param string $key
     * @param out mixed $value
     * @return false if the $key is not found in cache ($value is not modified in this case)
     */
    abstract function get($key, &$value);

    /**
     * Sets a key/value pair in the persistent cache.
     * @param string $key - it should be the return value of makeKey function
     * @param mixed $value
     * @param int $lifetime
     * @return false on error
     */
    abstract function set($key, $value, $lifetime = null);

    /**
     * Purge the persistent cache.
     * @param boolean $all - if false only expired items will be purged
     */
    abstract function purge($all);
}
