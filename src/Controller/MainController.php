<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {

    	$httpClient = HttpClient::create();
    	$base_url = 'http://localhost:8000';
		$response = $httpClient->request('GET', $base_url.'/api/luminaires', ['headers' => ['accept' =>'application/json']]);

		$statusCode = $response->getStatusCode();
		$contentType = $response->getHeaders()['content-type'][0];
		// $content = $response->getContent();
		$luminaires = $response->toArray();
		$list = array();
		foreach ($luminaires as $l) {
			$list[] = $l['address'];
		}
		// $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'luminaires' => $luminaires//implode(", ", $list),
        ]);
    }
}
