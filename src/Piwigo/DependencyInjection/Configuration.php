<?php
namespace Piwigo\DependencyInjection;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class Configuration extends FileLoader
{
    public function load($resource, $type = null)
    {
        $configs = array();

        if (is_array($resource))
        {
            foreach ($resource as $r)
            {
                if ($this->supports($r))
                {
                    $configs[] = Yaml::parse(file_get_contents($r));
                }
            }
        }
        else
        {
            if ($this->supports($resource))
            {
                $configs[] = Yaml::parse(file_get_contents($resource));
            }
        }
        
        // maybe import some other resource:
        // $this->import('extra_users.yml');

        return $configs[0];
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
