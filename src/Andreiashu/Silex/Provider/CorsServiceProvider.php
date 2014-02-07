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
     * @var \Asm89\Stack\CorsService
     */
    private $cors;

    /**
     * @param array $options
     */
    public function __construct($options = array()) {
        $this->options = array_merge(array(
            'response_class' => '\Symfony\Component\HttpFoundation\Response',
        ), $options);
    }

    public function register(Application $app) {}

    /**
     * @param Application $app
     */
    public function boot(Application $app) {
        $this->cors = new CorsService($this->options);

        // handle OPTIONS preflight request if necessary
        $app->before(function (Request $request) use ($app) {
            if (!$this->cors->isCorsRequest($request)) {
                return;
            }

            if ($this->cors->isPreflightRequest($request)) {
                $response = $this->cors->handlePreflightRequest($request);
                if (!empty($this->options['response_class'])) {
                    $response = new $this->options['response_class'](
                        $response->getContent(),
                        $response->getStatusCode(),
                        $response->headers
                    );
                }

                return $response;
            }

            if (!$this->cors->isActualRequestAllowed($request)) {
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
        $app->after(function (Request $request, Response $response) {
            if (!$this->cors->isCorsRequest($request)) {
                return;
            }
            $this->cors->addActualRequestHeaders($response, $request);
        });
    }
}
