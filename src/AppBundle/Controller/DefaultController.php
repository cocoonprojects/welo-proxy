<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Zend\Diactoros\Response;

class DefaultController extends Controller
{
    public function indexAction(ServerRequestInterface $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    public function proxyAction($catchall, ServerRequestInterface $request)
    {
        $legacyBaseUrl = $this->getParameter('welo_legacy_base_url');
        $authHeaderName = $this->getParameter('welo_legacy_auth_header_name');
        $client = new Client();

        $headers = [];
        $headers['Content-Type'] = 'application/json';

        if ($request->hasHeader($authHeaderName)) {
            $headers[$authHeaderName] = $request->getHeader($authHeaderName);
        }

        $proxyRequest = new Request(
            $request->getMethod(),
            "{$legacyBaseUrl}/{$catchall}",
            $headers,
            $request->getBody()
        );

        try {

            $proxyResponse = $client->send($proxyRequest);

        } catch (BadResponseException $e) {

            if ($e->hasResponse()) {
                return new Response(
                    $e->getResponse()->getBody(),
                    $e->getResponse()->getStatusCode(),
                    $e->getResponse()->getHeaders()
                );
            }

            return new Response($e->getMessage(), 500);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }

        $response = new Response(
            $proxyResponse->getBody(),
            $proxyResponse->getStatusCode(),
            $proxyResponse->getHeaders()
        );

        return $response;
    }


}
