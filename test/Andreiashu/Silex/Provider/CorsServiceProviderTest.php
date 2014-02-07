<?php

namespace Andreiashu\Silex\Provider;


use Silex\Application;
use Silex\ServiceProviderInterface;
use Asm89\Stack\CorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsServiceProviderTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_returns_normal_response_for_valid_request() {
        $app = $this->createSilexApp();
        $app->get('/', function() {
            return 'OK';
        });
        $request = $this->createValidActualRequest();
        $response = $app->handle($request);
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_it_returns_json_response_for_request_with_origin_not_allowed() {
        $app = $this->createSilexApp(array(
            'response_class' => '\Symfony\Component\HttpFoundation\JsonResponse',
            'allowedOrigins' => array('notlocalhost'),
        ));

        $request = $this->createValidActualRequest();
        $response = $app->handle($request);
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_it_returns_403_json_response_for_preflight_request_with_origin_not_allowed()
    {
        $app = $this->createSilexApp(array(
            'response_class' => '\Symfony\Component\HttpFoundation\JsonResponse',
            'allowedOrigins' => array('notlocalhost'),
        ));
        $request = $this->createValidPreflightRequest();
        $response = $app->handle($request);
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    private function createValidActualRequest()
    {
        $request  = new Request();
        $request->headers->set('Origin', 'localhost');

        return $request;
    }

    private function createValidPreflightRequest()
    {
        $request  = new Request();
        $request->headers->set('Origin', 'localhost');
        $request->headers->set('Access-Control-Request-Method', 'get');
        $request->setMethod('OPTIONS');

        return $request;
    }

    /**
     * @param array $options
     * @return Application
     */
    private function createSilexApp(array $options = array())
    {
        $passedOptions = array_merge(array(
                'allowedHeaders'      => array('x-allowed-header', 'x-other-allowed-header'),
                'allowedMethods'      => array('delete', 'get', 'post', 'put'),
                'allowedOrigins'      => array('localhost'),
                'exposedHeaders'      => false,
                'maxAge'              => false,
                'supportsCredentials' => false,
            ),
            $options
        );

        $app = new Application();
        $app->register(new CorsServiceProvider($passedOptions));
        return $app;
    }
}
