<?php
namespace Piwigo\Http\Encoder;

use Piwigo\Http\Encoder\AbstractResponseEncoder;

class XmlRpcEncoder extends AbstractResponseEncoder
{
    function encodeResponse($response)
    {
        $respClass = @get_class($response);
    
        if ($respClass == 'Piwigo\Utils\Error')
        {
            $code = $response->code();
            $msg = htmlspecialchars($response->message());
            $ret = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$msg}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>
EOD;
    
            return $ret;
        }

        parent::flattenResponse($response);
        $ret = $this->xmlrpcEncode($response);
        $ret = <<<EOD
<methodResponse>
  <params>
    <param>
      <value>
        $ret
      </value>
    </param>
  </params>
</methodResponse>
EOD;
    
        return $ret;
    }

    function getContentType()
    {
        return 'text/xml';
    }

    function xmlrpcEncode($data)
    {
        switch (gettype($data))
        {
        case 'boolean':
            return '<boolean>'.($data ? '1' : '0').'</boolean>';
        case 'integer':
            return '<int>'.$data.'</int>';
        case 'double':
            return '<double>'.$data.'</double>';
        case 'string':
            return '<string>'.htmlspecialchars($data).'</string>';
        case 'object':
        case 'array':
            $is_array = range(0, count($data) - 1) === array_keys($data);
      
            if ($is_array)
            {
                $return = '<array><data>'."\n";
        
                foreach ($data as $item)
                {
                    $return .= '  <value>'.$this->xmlrpcEncode($item)."</value>\n";
                }
        
                $return .= '</data></array>';
            }
            else
            {
                $return = '<struct>'."\n";
        
                foreach ($data as $name => $value)
                {
                    $name    = htmlspecialchars($name);
                    $return .= "  <member><name>$name</name><value>";
                    $return .= $this->xmlrpcEncode($value)."</value></member>\n";
                }
        
                $return .= '</struct>';
            }
      
            return $return;
        }
    }
}