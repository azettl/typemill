<?php

namespace Typemill\Controllers;

/* Use the slim-container */
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Container\ContainerInterface;
use Typemill\Events\OnPageReady;

abstract class Controller
{
	protected $c;

	public function __construct(ContainerInterface $c)
	{
		$this->c 	= $c;
		$this->c->dispatcher->dispatch('onTwigLoaded');
	}
	
	# frontend rendering
	protected function render($response, $route, $data)
	{
		# why commented this out??
		$data = $this->c->dispatcher->dispatch('onPageReady', new OnPageReady($data))->getData();

		if(isset($_SESSION['old']))
		{
			unset($_SESSION['old']);
		}
		
		$response = $response->withoutHeader('Server');
		if($this->c->request->getUri()->getScheme() == 'https')
		{
			$response = $response->withAddedHeader('Strict-Transport-Security', 'max-age=63072000');
		}

		$response = $response->withAddedHeader('X-Content-Type-Options', 'nosniff');
		$response = $response->withAddedHeader('X-Frame-Options', 'SAMEORIGIN');
		$response = $response->withAddedHeader('X-XSS-Protection', '1;mode=block');
		$response = $response->withAddedHeader('Referrer-Policy', 'no-referrer-when-downgrade');
		$response = $response->withAddedHeader('X-Powered-By', 'Typemill');
		

		return $this->c->view->render($response, $route, $data);
	}
	
	protected function render404($response, $data = NULL)
	{
		return $this->c->view->render($response->withStatus(404), '/404.twig', $data);
	}
}