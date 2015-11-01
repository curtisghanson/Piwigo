<?php
namespace Piwigo\Http;

use Piwigo\Http\AbstractRequestHandler;
use Piwigo\Utils\Error;

class RestRequestHandler extends AbstractRequestHandler
{
    function handleRequest(&$service)
    {
        $params = array();

        $param_array = $service->isPost() ? $_POST : $_GET;
    
        foreach ($param_array as $name => $value)
        {
            if ($name=='format')
            {
                // ignore - special keys
                continue;
            }

            if ($name=='method')
            {
                $method = $value;
            }
            else
            {
                $params[$name]=$value;
            }
        }
        
        if ( empty($method) && isset($_GET['method']) )
        {
            $method = $_GET['method'];
        }

        if ( empty($method) )
        {
            $service->sendResponse(new Error(WS_ERR_INVALID_METHOD, 'Missing "method" name'));
      
            return;
        }
    
        $resp = $service->invoke($method, $params);
        $service->sendResponse($resp);
    }
}
