<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GuzzleHttp\Psr7\Request;

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
        $headers[$authHeaderName] = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJ1aWQiOiI2MDAwMDAwMC0wMDAwLTAwMDAtMDAwMC0wMDAwMDAwMDAwMDAiLCJpYXQiOiIxNDM4NzgyOTU1In0.rqGFFeOf5VdxO_qpz_fkwFJtgJH4Q5Kg6WUFGA_L1tMB-yyZj7bH3CppxxxvpekQzJ7y6aH6I7skxDh1K1Cayn3OpyaXHyG9V_tlgo08TKR7EK0TsBA0vWWiT7Oito97ircrw_4N4ZZFmF6srpNHda2uw775-7SpQ8fdI0_0LOn1IwF1MKvJIuZ9J7bR7PZsdyqLQSpNm8P5gJiA0c6i_uubtVEljVvr1H1mSoq6hViS9A2M-v4THlbH_Wki2pYp00-ggUu6dm25NeX300Q6x2RBHVY_bXpw7voRbXI1VAg_LxXDjv61l4lar6dOhK3qbsXm9P2JTEqyG7bYSAqtLA';

        $proxyRequest = new Request(
            $request->getMethod(),
            "{$legacyBaseUrl}/{$catchall}",
            $headers,
            $request->getBody()
        );

        return $client->send($proxyRequest);
    }


}
