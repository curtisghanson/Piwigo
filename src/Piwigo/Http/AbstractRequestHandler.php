<?php
namespace Piwigo\Http;

/**
 * Abstract base class for request handlers.
 */
abstract class AbstractRequestHandler
{
    /** Virtual abstract method. Decodes the request (GET or POST) handles the
     * method invocation as well as response sending.
     */
    abstract function handleRequest(&$service);
}
