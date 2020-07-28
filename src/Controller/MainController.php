<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;


use App\Entity\Controller;
use App\Entity\Luminaire;
use App\Entity\Recipe;
use App\Entity\Run;



use App\Form\ControllerType;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

    	$controllers = $user->getControllers();
    	$luminaires = $user->getLuminaires();

    	$em = $this->getDoctrine()->getManager();

    	$controller = new Controller;
    	$controller->addUser($user);
        $form = $this->createForm(ControllerType::class, $controller);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        	$em->persist($controller);
        	$em->flush();

        	return $this->redirectToRoute('home');
        }
    	
  //   	$httpClient = HttpClient::create();
  //   	$base_url = 'http://localhost:8000';
		// $response = $httpClient->request('GET', $base_url.'/api/luminaires', ['headers' => ['accept' =>'application/json']]);

		// $statusCode = $response->getStatusCode();
		// $contentType = $response->getHeaders()['content-type'][0];
		// // $content = $response->getContent();
		// $luminaires = $response->toArray();
		// $list = array();
		// foreach ($luminaires as $l) {
		// 	$list[] = $l['address'];
		// }
		// $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'luminaires' => $luminaires,//implode(", ", $list),
            'controllers' => $controllers,
            'form' => $form->createView(),
        ]);
    }

		/**
		 * @Route("/controller/view/{id}", name="view-controller")
		 */
		public function viewController(Request $request, Controller $controller)
		{
			$em = $this->getDoctrine()->getManager();

			$luminaire_repo = $this->getDoctrine()->getRepository(Luminaire::class);
			$run_repo = $this->getDoctrine()->getRepository(Run::class);

			$x_max = $luminaire_repo->getXMax($controller);
        	$y_max = $luminaire_repo->getYMax($controller);
        	$clusters = $controller->getClusters();

        	// Edit controller
	        $form = $this->createForm(ControllerType::class, $controller);
	        $form->handleRequest($request);
	        if ($form->isSubmitted() && $form->isValid()) {
	        	$em->flush();

	        	return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
	        }

			return $this->render('main/controller.html.twig', [
	            'controller' => $controller,
	            'form' => $form->createView(),
	            'x_max' => $x_max['x_max'],
	            'y_max' => $y_max['y_max'],
	            'luminaire_repo' => $luminaire_repo,
	            'run_repo' => $run_repo,
	            'clusters' => $clusters,
        ]);
		}

		/**
		 * @Route("/controller/edit/{id}", name="edit-controller")
		 */
		public function editController(Request $request, Controller $controller)
		{
				

			return $this->render('main/controller.html.twig', [
	            'controller' => $controller,
        ]);
		}

		/**
		 * @Route("/lightings/view", name="view-lightings")
		 */
		public function viewLightings(Request $request)
		{
			$auth_checker = $this->get('security.authorization_checker');
        	$token = $this->get('security.token_storage')->getToken();
        	$user = $token->getUser();

    		$em = $this->getDoctrine()->getManager();

    		$controllers = $user->getControllers();
			$luminaires = $user->getLuminaires();

			return $this->render('main/luminaires.html.twig', [
	            'luminaires' => $luminaires,
	            'controllers' => $controllers,
        ]);
		}

		/**
		 * @Route("/recipes/view", name="view-recipes")
		 */
		public function viewRecipes(Request $request)
		{
			$auth_checker = $this->get('security.authorization_checker');
        	$token = $this->get('security.token_storage')->getToken();
        	$user = $token->getUser();

    		$em = $this->getDoctrine()->getManager();

    		$recipes = $user->getRecipes();

			return $this->render('main/recipes.html.twig', [
	            'recipes' => $recipes,
        ]);
		}

		/**
		 * @Route("/programs/view", name="view-programs")
		 */
		public function viewPrograms(Request $request)
		{
			$auth_checker = $this->get('security.authorization_checker');
        	$token = $this->get('security.token_storage')->getToken();
        	$user = $token->getUser();

    		$em = $this->getDoctrine()->getManager();

    		$programs = $user->getPrograms();

			return $this->render('main/programs.html.twig', [
	            'programs' => $programs,
        ]);
		}

		/**
		 * @Route("/runs/view/{controller}", name="view-runs")
		 */
		public function viewRuns(Request $request, Controller $controller)
		{
			$auth_checker = $this->get('security.authorization_checker');
        	$token = $this->get('security.token_storage')->getToken();
        	$user = $token->getUser();

    		$em = $this->getDoctrine()->getManager();

    		$runs = $this->getDoctrine()->getRepository(Run::class)->getAllByController($controller);

			return $this->render('main/runs.html.twig', [
	            'runs' => $runs,
	            'controller' => $controller
        	]);
		}

	    /**
	     * @Route("/luminaires/post/{luminaire}:{controller}", name="post-luminaire")
	     */
	    public function postLuminaire(Request $request, Luminaire $luminaire, Controller $controller)
	    {
	    	$session = new Session;

			$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

	    	$normalizer = new ObjectNormalizer($classMetadataFactory);
			$serializer = new Serializer([$normalizer]);

			$data = $serializer->normalize($luminaire, null, ['groups' => 'luminaire']);

			// $jsonContent = $serializer->serialize($luminaire, 'json');

			// die(print_r($data));
	    	
	    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);
    		$base_url = $controller->getUrl();
			$response = $httpClient->request('POST', $base_url.'/api/luminaires', 
				['json' => $data]
			);

			$statusCode = $response->getStatusCode();

			if ($statusCode == 201) {
				$session->getFlashBag()->add(
                    'info',
                    'Lighting '.$luminaire->getAddress().' successfully added !'
                );
			} else {
				$session->getFlashBag()->add(
                    'info',
                    'Something went wrong, maybe this lighting '.$luminaire->getAddress().' already exists remotely !'
                );
			}
			

			

			return $this->redirectToRoute('home');
	    }


}
