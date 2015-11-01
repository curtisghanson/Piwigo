<?php
namespace Piwigo\Http\Encoder;

use Piwigo\Http\Encoder\AbstractResponseEncoder;

class JsonEncoder extends AbstractResponseEncoder
{
    function encodeResponse($response)
    {
        $respClass = strtolower( @get_class($response) );
    
        if ($respClass=='pwgerror')
        {
            return json_encode(array(
                'stat'    => 'fail',
                'err'     => $response->code(),
                'message' => $response->message(),
            ));
        }
    
        parent::flattenResponse($response);
    
        return json_encode(array(
            'stat'   => 'ok',
            'result' => $response,
        ));
    }

    function getContentType()
    {
        return 'text/plain';
    }
}
