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
     *   'response_class' eg:
     *     => '\Symfony\Component\HttpFoundation\JsonResponse'
     */
    public function __construct($options = array()) {
        $this->options = $options;
    }

    public function register(Application $app) {}

    /**
     * @param Application $app
     */
    public function boot(Application $app) {
        $cors = new CorsService($this->options);

        // handle OPTIONS preflight request if necessary
        $app->before(function (Request $request) use ($app, $cors) {
            if (!$cors->isCorsRequest($request)) {
                return;
            }

            if ($cors->isPreflightRequest($request)) {
                $response = $cors->handlePreflightRequest($request);
                if (!empty($this->options['response_class'])) {
                    $response = new $this->options['response_class'](
                        $response->getContent(),
                        $response->getStatusCode(),
                        $response->headers->all()
                    );
                }

                return $response;
            }

            if (!$cors->isActualRequestAllowed($request)) {
                if (!empty($this->options['response_class'])) {
                    $response = new $this->options['response_class'](
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
