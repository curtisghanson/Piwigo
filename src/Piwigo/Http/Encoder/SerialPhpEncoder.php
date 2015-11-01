<?php
namespace Piwigo\Http\Encoder;

use Piwigo\Http\Encoder\AbstractResponseEncoder;

class SerialPhpEncoder extends AbstractResponseEncoder
{
    function encodeResponse($response)
    {
        $respClass = strtolower( @get_class($response) );
    
        if ($respClass=='pwgerror')
        {
            return serialize(array(
                'stat'    => 'fail',
                'err'     => $response->code(),
                'message' => $response->message(),
            ));
        }
    
        parent::flattenResponse($response);
    
        return serialize(array(
            'stat'   => 'ok',
            'result' => $response
        ));
    }

    function getContentType()
    {
        return 'text/plain';
    }
}
