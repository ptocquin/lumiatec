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
		$response = $httpClient->request('GET', 'http://localhost:8000/api/luminaires', ['headers' => ['accept' =>'application/json']]);

		$statusCode = $response->getStatusCode();
		// $statusCode = 200
		$contentType = $response->getHeaders()['content-type'][0];
		// $contentType = 'application/json'
		$content = $response->getContent();
		// $content = '{"id":521583, "name":"symfony-docs", ...}'
		// $content = $response->toArray();
		// $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'content' => $content,
        ]);
    }
}
