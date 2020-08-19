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
use App\Entity\Log;



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

    	// $controller = new Controller;
    	// $controller->addUser($user);
        $form = $this->createForm(ControllerType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        	$data = $form->getData();
        	$controller = $this->getDoctrine()->getRepository(Controller::class)->findOneByUrl($data->getUrl());
        	if(is_null($controller)){
        		$controller = new Controller;
       			$controller->setUrl($data->getUrl());
       			$controller->setName($data->getName());
       			$controller->setAuthToken($data->getAuthToken());
       			$controller->addUser($user);
       			$em->persist($controller);
        	} else {
        		$controller->addUser($user);
        	}
        	$em->flush();

        	return $this->redirectToRoute('home');
        }

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'luminaires' => $luminaires,//implode(", ", $list),
            'controllers' => $controllers,
            'form' => $form->createView(),
            'logs' => $logs,
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
		$log_repo = $this->getDoctrine()->getRepository(Log::class);

		$luminaires = $luminaire_repo->findAll();
		$data = array();
		foreach ($luminaires as $luminaire) {
			$logs = $luminaire->getLogs();
			$label1 = $luminaire->getAddress().' light';
			$label2 = $luminaire->getAddress().' temp';
			if(!is_null($logs)){
				$d1 = array();
				$d2 = array();
				foreach ($logs as $log) {
					$values = $log->getValue();
					$x = date_format($log->getTime(), "Y-m-d H:i:s");
					// $x = $log->getTime();
					if(empty($values['channels_on'])){
						$y = 0;
					} else {
						$y = 100;
					}
					$y2 = ($values['led_pcb_0']+$values['led_pcb_1'])/2 ;

					$d1[] = array('x' => $x, 'y' => $y);
					$d2[] = array('x' => $x, 'y' => $y2);
				}
			}
			#https://www.w3schools.com/colors/colors_palettes.asp
			if(!empty($d1)){
				$data[] = array('label' => $label1, 'data' => $d1, 'showLine' => true, 'fill' => false, 'borderColor' => '#b2b2b2', 'lineTension' => 0);
			}
			if(!empty($d2)){
				$data[] = array('label' => $label2, 'data' => $d2, 'showLine' => true, 'fill' => false, 'borderColor' => '#f4e1d2', 'lineTension' => 1);
			}
		}

		$dataset = array('datasets' => $data);

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
            'log_repo' => $log_repo,
            'clusters' => $clusters,
            'dataset' => $dataset
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
	 * @Route("/controller/delete/{id}", name="delete-controller")
	 */
	public function deleteController(Request $request, Controller $controller)
	{
		$em = $this->getDoctrine()->getManager();

		$lightings = $controller->getLuminaires();
		foreach ($lightings as $lighting) {
				$controller->removeLuminaire($lighting);
			}
		$em->remove($controller);
		$em->flush();

		return $this->redirectToRoute('home');
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
