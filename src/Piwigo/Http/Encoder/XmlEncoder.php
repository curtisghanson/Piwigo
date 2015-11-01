<?php
namespace Piwigo\Http\Encoder;

class XmlEncoder
{
    private $indent;
    private $indentStr;
    private $elementStack;
    private $lastTagOpen;
    private $indentLevel;
    private $encodedXml;

    function __construct()
    {
        $this->elementStack = array();
        $this->lastTagOpen  = false;
        $this->indentLevel  = 0;
        $this->encodedXml   = '';
        $this->indent       = true;
        $this->indentStr    = "\t";
    }

    function &getOutput()
    {
        return $this->encodedXml;
    }


    function start_element($name)
    {
        $this->_end_prev(false);
        if (!empty($this->elementStack))
        {
            $this->_eol_indent();
        }
        $this->indentLevel++;
        $this->_indent();
        $diff = ord($name[0])-ord('0');
        if ($diff>=0 && $diff<=9)
        {
            $name='_'.$name;
        }
        $this->_output( '<'.$name );
        $this->lastTagOpen = true;
        $this->elementStack[] = $name;
    }

    function end_element($x)
    {
        $close_tag = $this->_end_prev(true);
        $name = array_pop( $this->elementStack );
        if ($close_tag)
        {
            $this->indentLevel--;
            $this->_indent();
//      $this->_eol_indent();
            $this->_output('</'.$name.">");
        }
    }

    function write_content($value)
    {
        $this->_end_prev(false);
        $value = (string)$value;
        $this->_output( htmlspecialchars( $value ) );
    }

    function write_cdata($value)
    {
        $this->_end_prev(false);
        $value = (string)$value;
        $this->_output(
            '<![CDATA['
            . str_replace(']]>', ']]&gt;', $value)
            . ']]>' );
    }

    function write_attribute($name, $value)
    {
        $this->_output(' '.$name.'="'.$this->encode_attribute($value).'"');
    }

    function encode_attribute($value)
    {
        return htmlspecialchars( (string)$value);
    }

    function _end_prev($done)
    {
        $ret = true;
        if ($this->lastTagOpen)
        {
            if ($done)
            {
                $this->indentLevel--;
                $this->_output( ' />' );
                //$this->_eol_indent();
                $ret = false;
            }
            else
            {
                $this->_output( '>' );
            }
            $this->lastTagOpen = false;
        }
        return $ret;
    }

    function _eol_indent()
    {
        if ($this->indent)
            $this->_output("\n");
    }

    function _indent()
    {
        if ($this->indent and
                $this->indentLevel > count($this->elementStack) )
        {
            $this->_output(
                str_repeat( $this->indentStr, count($this->elementStack) )
             );
        }
    }

    function _output($raw_content)
    {
        $this->encodedXml .= $raw_content;
    }
}
