<?php

namespace Andreiashu\Silex\Provider;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class that can be used instead of the normal Response one to signal other
 * hooks (like ->after) that we have a cors error
 */
class CorsServiceDeniedResponse extends Response
{
}
