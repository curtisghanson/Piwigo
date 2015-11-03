<?php
namespace Piwigo\Mailer;

use Piwigo\Application;

class Mailer
{
    private $conf;

    public function __construct(Application $app)
    {
        $this->conf = $app['conf']['mail'];
    }

    /**
     * Returns the name of the mail sender
     *
     * @return string
     */
    function getSenderName()
    {
      return (empty($conf['mail_sender_name']) ? $conf['default_sender_name'] : $conf['mail_sender_name']);
    }
}