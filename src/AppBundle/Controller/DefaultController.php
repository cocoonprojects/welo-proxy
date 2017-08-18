<?php

namespace AppBundle\Controller;

use AppBundle\FakeCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return new JsonResponse(['aloha']);
    }

    public function eventAction()
    {
        $command = new FakeCommand('ciaone', 'banana');

        $this->get('broadway.command_handling.command_bus')
             ->dispatch($command);


        return new Response('<html><head></head><body>ciaone</body></html>');
    }

    public function eventLoadAction()
    {
        $evs = $this->get('broadway.event_store')->load('00000000-0000-0000-0000-000000000111');

        dump($evs);

        return new Response('<html><head></head><body>ciaone</body></html>');
    }
}
