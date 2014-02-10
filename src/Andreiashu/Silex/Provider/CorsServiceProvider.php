<?php

namespace Andreiashu\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Asm89\Stack\CorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     * Additional options vs CorsStack
     *   'denied_reponse_class' eg:
     *     => '\Symfony\Component\HttpFoundation\JsonResponse'
     *     => '\Andreiashu\Silex\Provider\CorsServiceDeniedResponse'
     */
    public function __construct($options = array()) {
        $this->options = array_merge(array(
            // if specified, this class will be returned in case
            // a preflight or a normal requests are not allowed
            'denied_reponse_class' => null,
        ), $options);
    }

    public function register(Application $app) {}

    /**
     * @param Application $app
     */
    public function boot(Application $app) {
        $options = $this->options;
        $cors = new CorsService($options);

        // handle OPTIONS preflight request if necessary
        $app->before(function (Request $request) use ($app, $cors, $options) {
            if (!$cors->isCorsRequest($request)) {
                return;
            }

            if ($cors->isPreflightRequest($request)) {
                $response = $cors->handlePreflightRequest($request);
                $denied_codes = array(Response::HTTP_METHOD_NOT_ALLOWED, Response::HTTP_FORBIDDEN);
                $is_denied = in_array($response->getStatusCode(), $denied_codes);
                if ($is_denied && !empty($options['denied_reponse_class'])) {
                    $response = new $options['denied_reponse_class'](
                        $response->getContent(),
                        $response->getStatusCode(),
                        $response->headers->all()
                    );
                }

                return $response;
            }

            if (!$cors->isActualRequestAllowed($request)) {
                if (!empty($options['denied_reponse_class'])) {
                    $response = new $options['denied_reponse_class'](
                        'Not allowed',
                        403
                    );
                }
                else {
                    $response = new Response('Not allowed.', 403);
                }

                return $response;
            }
        }, Application::EARLY_EVENT);

        // when the response is sent back, add CORS headers if necessary
        $app->after(function (Request $request, Response $response) use ($cors) {
            if (!$cors->isCorsRequest($request)) {
                return;
            }
            $cors->addActualRequestHeaders($response, $request);
        });
    }
}
