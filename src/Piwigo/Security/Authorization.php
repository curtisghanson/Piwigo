<?php
namespace Piwigo\Security;

class Authorization
{
    const ACCESS_FREE          = 0;
    const ACCESS_GUEST         = 1;
    const ACCESS_CLASSIC       = 2;
    const ACCESS_ADMINISTRATOR = 3;
    const ACCESS_WEBMASTER     = 4;
    const ACCESS_CLOSED        = 5;
}