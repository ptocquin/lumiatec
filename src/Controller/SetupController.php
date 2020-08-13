<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;



use App\Entity\Controller;
use App\Entity\Luminaire;
use App\Entity\Pcb;
use App\Entity\Channel;
use App\Entity\Led;
use App\Entity\Cluster;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\Program;
use App\Entity\Step;
use App\Entity\Log;
use App\Entity\Run;
use App\Entity\RunStep;


use App\Form\ControllerType;
use App\Form\RecipeType;
use App\Form\ProgramType;
use App\Form\StepType;
use App\Form\LuminaireType;


/**
 * @Route("/setup")
 */
class SetupController extends AbstractController
{
    /**
     * @Route("/", name="setup")
     */
    public function index(Request $request)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

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

        return $this->render('setup/index.html.twig', [
            'controller_name' => 'SetupController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/check-controllers", name="check-controllers")
     */
    public function checkControllers(Request $request)
    {
    	$session = new Session;

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

    	$em = $this->getDoctrine()->getManager();

    	$controllers = $user->getControllers();

    	foreach ($controllers as $controller) {

    		$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);
	    	$base_url = $controller->getUrl();

			try {
				$response = $httpClient->request('GET', $base_url.'/api/luminaires', ['headers' => ['accept' =>'application/json'], 'timeout' => 2]);
				$statusCode = $response->getStatusCode();

			} catch (\Exception $e) {
				$controller->setStatus(1);
				$em->flush();
				$session->getFlashBag()->add(
			        'info',
			        'The controller ('.$controller->getName().') was not reachable... '
			    );
			    continue;
			}

			$controller->setStatus(0);

			if ($statusCode != 200) {
				$session->getFlashBag()->add(
			        'info',
			        'The request failed with status code: '.$statusCode
			    );
			    continue;
				
			}
		}

		return $this->redirectToRoute('home');
	}

    /**
     * @Route("/sync-controllers", name="sync-controllers")
     */
    public function syncControllers(Request $request)
    {
    	$session = new Session;

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

    	$em = $this->getDoctrine()->getManager();

    	$controllers = $user->getControllers();
    	$c_count = 0; // compteur controllers
    	$l_count = 0; // compteur luminaires totaux
    	$r_count = 0; // compteur des recettes totales
    	$ru_count = 0; // compteur des runs totaux
    	$p_count = 0; // compteur des programmes totaux
    	$p_added_count = 0; // compteur des programmes ajoutés
    	$r_added_count = 0; // compteur des recettes ajoutées
    	$ru_added_count = 0; // compteur des runs ajoutés
    	$l_added_count = 0; // compteur luminaires ajoutés (nouveaux)

    	foreach ($controllers as $controller) {

    		$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);
	    	$base_url = $controller->getUrl();

			try {
				$response = $httpClient->request('GET', $base_url.'/api/luminaires', ['headers' => ['accept' =>'application/json'], 'timeout' => 2]);
				$statusCode = $response->getStatusCode();

			} catch (\Exception $e) {
				$controller->setStatus(1);
				$em->flush();
				$session->getFlashBag()->add(
			        'info',
			        'The controller ('.$controller->getName().') was not reachable... '
			    );
			    continue;
			}

			$controller->setStatus(0);

			if ($statusCode != 200) {
				$session->getFlashBag()->add(
			        'info',
			        'The request failed with status code: '.$statusCode
			    );
			    continue;
				
			}

			// On dissocie les luminaires précédemment associés avec ce controller
    		$old_luminaires = $controller->getLuminaires();
    		foreach ($old_luminaires as $l) {
    			$l->setController(null);
    			$em->persist($l);
    		}
    		$em->flush();

    		$c_count += 1;
			// $contentType = $response->getHeaders()['content-type'][0];
			// $content = $response->getContent();
			$luminaires = $response->toArray();
			foreach ($luminaires as $l) {
				$l_count += 1;

				if(is_null($l['cluster'])){
					// $cluster = $this->getDoctrine()->getRepository(Cluster::class)->findOneBy(array(
					// 	'label' => 1,
					// 	'controller' => $controller,
					// ));
					// if(is_null($cluster)){
					// 	$cluster = new Cluster;
					// 	$cluster->setLabel(1);
					// 	$cluster->setController($controller);
					// 	$em->persist($cluster);
					// 	$em->flush();
					// }
					$cluster = null;
				} else {
					$cluster = $this->getDoctrine()->getRepository(Cluster::class)->findOneBy(array(
						'label' => $l['cluster']['label'],
						'controller' => $controller,
					));
					if(is_null($cluster)){
						$cluster = new Cluster;
						$cluster->setLabel($l['cluster']['label']);
						$cluster->setController($controller);
						$em->persist($cluster);
						$em->flush();
					}
				}

				// Luminaires
				if(is_null($this->getDoctrine()->getRepository(Luminaire::class)->findOneByAddress($l['address']))) {
					$luminaire = new Luminaire;
					$luminaire->setAddress($l['address']);
					$luminaire->setSerial($l['serial']);
					$luminaire->setLigne($l['ligne']);
					$luminaire->setColonne($l['colonne']);
					$luminaire->setController($controller);
					$luminaire->addUser($user);
					$luminaire->setCluster($cluster);
					$em->persist($luminaire);
				// Pcb
					foreach($l['pcbs'] as $p){
						$pcb = new Pcb;
						$pcb->setCrc($p['crc']);
						$pcb->setSerial($p['serial']);
						$pcb->setN($p['n']);
						$pcb->setType($p['type']);
						$em->persist($pcb);
						$luminaire->addPcb($pcb);
					}

				// Channels
					foreach ($l['channels'] as $c) {
						$channel = new Channel;
						$channel->setChannel($c['channel']);
						$channel->setIPeek($c['iPeek']);
						$channel->setLuminaire($luminaire);
						
				// Leds
						# Vérifie que la Led existe dans la base de données, sinon l'ajoute.
                        $led = $this->getDoctrine()->getRepository(Led::class)->findOneBy(array(
                            'wavelength' => $c['led']['wavelength'],
                            'type' => $c['led']['type'],
                            'manufacturer' => $c['led']['manufacturer']));

                        if(is_null($led)) {
                        	$led = new Led;
							$led->setWavelength($c['led']['wavelength']);
							$led->setType($c['led']['type']);
							$led->setManufacturer($c['led']['manufacturer']);
							$em->persist($led);
							$em->flush();
							$channel->setLed($led);
                        } else {
                        	$led->addChannel($channel);
                        }
						$em->persist($channel);
					} // foreach channel

					$l_added_count += 1;
				} else {
					$luminaire = $this->getDoctrine()->getRepository(Luminaire::class)->findOneByAddress($l['address']);
					$luminaire->setController($controller);
					$luminaire->setCluster($cluster);
					$luminaire->setLigne($l['ligne']);
					$luminaire->setColonne($l['colonne']);
					// $em->persist($luminaire);
				}
			}

			$em->flush();

			// Sync Recipes
			try {
				$response = $httpClient->request('GET', $base_url.'/api/recipes', ['headers' => ['accept' =>'application/json'], 'timeout' => 20]);
				$statusCode = $response->getStatusCode();

			} catch (\Exception $e) {
				$controller->setStatus(1);
				$em->flush();
				$session->getFlashBag()->add(
			        'info',
			        'The controller ('.$controller->getName().') was not reachable... '
			    );
			    continue;
			}

			if ($statusCode != 200) {
				$session->getFlashBag()->add(
			        'info',
			        'The request failed with status code: '.$statusCode
			    );
				
			}

			$recipes = $response->toArray();
			foreach ($recipes as $r) {
				$r_count += 1;
				$recipe = $this->getDoctrine()->getRepository(Recipe::class)->findOneBy(array('uuid' => $r['uuid'], 'user' => $user->getId()));
				if(is_null($recipe)){
					$r_added_count += 1;
					$recipe = new Recipe;
					$recipe->setUuid($r['uuid']);
		            $recipe->setLabel($r['label']);
		            $recipe->setDescription($r['description']);
		            $recipe->setUser($user);
		            $recipe->setTimestamp($r['timestamp']);
		            foreach ($r['ingredients'] as $i) {

		                $led = $this->getDoctrine()->getRepository(Led::class)->findOneBy(
		                    array(
		                        "wavelength" => $i['led']['wavelength'],
		                        "type" => $i['led']['type'],
		                        "manufacturer" => $i['led']['manufacturer']
		                    )
		                );

		                if(is_null($led)) {
		                    $led = new Led;
		                    $led->setWavelength($i['led']['wavelength']);
		                    $led->setType($i['led']['type']);
		                    $led->setManufacturer($i['led']['manufacturer']);
		                    $em->persist($led);
		                }

		                $ingredient = new Ingredient;
		                $ingredient->setLed($led);
		                $ingredient->setLevel($i['level']);
		                $ingredient->setPwmStart($i['pwm_start']);
			            $ingredient->setPwmStop($i['pwm_stop']);
		                $em->persist($ingredient);
		                $recipe->addIngredient($ingredient);
		            }
		            $em->persist($recipe);
				} else {
					if($recipe->getTimestamp() < $r['timestamp']) {
						$recipe->setLabel($r['label']);
			            $recipe->setDescription($r['description']);
			            $recipe->setTimestamp($r['timestamp']);
			            foreach ($recipe->getIngredients() as $ingredient) {
			            	$em->remove($ingredient);
			            }

			            foreach ($r['ingredients'] as $i) {
			                $led = $this->getDoctrine()->getRepository(Led::class)->findOneBy(
			                    array(
			                        "wavelength" => $i['led']['wavelength'],
			                        "type" => $i['led']['type'],
			                        "manufacturer" => $i['led']['manufacturer']
			                    )
			                );

			                if(is_null($led)) {
			                    $led = new Led;
			                    $led->setWavelength($i['led']['wavelength']);
			                    $led->setType($i['led']['type']);
			                    $led->setManufacturer($i['led']['manufacturer']);
			                    $em->persist($led);
			                }
			                $ingredient = new Ingredient;
			                $ingredient->setLed($led);
			                $ingredient->setLevel($i['level']);
			                $ingredient->setPwmStart($i['pwm_start']);
			                $ingredient->setPwmStop($i['pwm_stop']);
			                $em->persist($ingredient);
			                $recipe->addIngredient($ingredient);
			            }
			            $em->persist($recipe);
					}
					if($recipe->getTimestamp() > $r['timestamp']) {
						$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
				    	$normalizer = new ObjectNormalizer($classMetadataFactory);
						$serializer = new Serializer([$normalizer]);

						$data = $serializer->normalize($recipe, null, ['groups' => 'recipe']);
						$data = array("recipe" => $data);

						// dd($data);
				    	
				    	$httpClient = HttpClient::create(['headers' => [
							    'X-AUTH-TOKEN' => $controller->getAuthToken(),
							]]);
			    		$base_url = $controller->getUrl();

			    		try {
							$response = $httpClient->request('POST', $base_url.'/remote/update/recipe', 
								['json' => $data]
							);
							$statusCode = $response->getStatusCode();
							$content = $response->getContent();
						} catch (\Exception $e) {
							$session->getFlashBag()->add(
						        'info',
						        'The controller ('.$controller->getName().') was not reachable... '
						    );
						    continue;


						}

						if ($statusCode == 200) {
							$session->getFlashBag()->add(
						        'info',
						        'ok !'
						    );
							
						} else {
							$session->getFlashBag()->add(
						        'info',
						        'error !'
						    );	
						}
					}
				}
			}

			$em->flush();

			// Sync Program
			try {
				$response = $httpClient->request('GET', $base_url.'/api/programs', ['headers' => ['accept' =>'application/json'], 'timeout' => 20]);
				$statusCode = $response->getStatusCode();

			} catch (\Exception $e) {
				$controller->setStatus(1);
				$em->flush();
				$session->getFlashBag()->add(
			        'info',
			        'The controller ('.$controller->getName().') was not reachable... '
			    );
			    continue;
			}

			if ($statusCode != 200) {
				$session->getFlashBag()->add(
			        'info',
			        'The request failed with status code: '.$statusCode
			    );
				
			}

			$programs = $response->toArray();
			foreach ($programs as $p) {
				$p_count += 1;
				$program = $this->getDoctrine()->getRepository(Program::class)->findOneBy(array('uuid' => $p['uuid'], 'user' => $user->getId()));
				if(is_null($program)){
					$p_added_count += 1;
					$program = new Program;
					$program->setUuid($p['uuid']);
					$program->setUser($user);
					$program->setLabel($p['label']);
					$program->setDescription($p['description']);
					$program->setTimestamp($p['timestamp']);
					foreach ($p['steps'] as $s) {
						$step = new Step;
						$step->setType($s['type']);
						$step->setRank($s['rank']);
						$step->setValue($s['value']);
						if(!is_null($s['recipe'])){
							$recipe = $this->getDoctrine()->getRepository(Recipe::class)->findOneBy(array('uuid' => $s['recipe']['uuid'], 'user' => $user->getId()));
							$step->setRecipe($recipe);
						}
						$em->persist($step);
						$program->addStep($step);
					}
					$em->persist($program);
				} else {
					if($program->getTimestamp() < $p['timestamp']) {
						$program->setUuid($p['uuid']);
						$program->setUser($user);
						$program->setLabel($p['label']);
						$program->setDescription($p['description']);
						$program->setTimestamp($p['timestamp']);
						foreach ($program->getSteps() as $step) {
							$em->remove($step);
						}
						foreach ($p['steps'] as $s) {
							$step = new Step;
							$step->setType($s['type']);
							$step->setRank($s['rank']);
							$step->setValue($s['value']);
							if(!is_null($s['recipe'])){
								$recipe = $this->getDoctrine()->getRepository(Recipe::class)->findOneBy(array('uuid' => $s['recipe']['uuid'], 'user' => $user->getId()));
								$step->setRecipe($recipe);
							}
							$em->persist($step);
							$program->addStep($step);
						}
					}
					if($program->getTimestamp() > $p['timestamp']) {
						$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
				    	$normalizer = new ObjectNormalizer($classMetadataFactory);
						$serializer = new Serializer([$normalizer]);

						$data = $serializer->normalize($program, null, ['groups' => 'program']);
						$data = array("program" => $data);

						// dd($data);
				    	
				    	$httpClient = HttpClient::create(['headers' => [
							    'X-AUTH-TOKEN' => $controller->getAuthToken(),
							]]);
			    		$base_url = $controller->getUrl();

			    		try {
							$response = $httpClient->request('POST', $base_url.'/remote/update/program', 
								['json' => $data]
							);
							$statusCode = $response->getStatusCode();
							$content = $response->getContent();
						} catch (\Exception $e) {
							$session->getFlashBag()->add(
						        'info',
						        'The controller ('.$controller->getName().') was not reachable... '
						    );

						    dd($e);
						    continue;


						}

						if ($statusCode == 200) {
							$session->getFlashBag()->add(
						        'info',
						        'ok !'
						    );
							
						} else {
							$session->getFlashBag()->add(
						        'info',
						        'error !'
						    );	
						}
					}
				}
			}

			$em->flush();

			// Sync Run
			try {
				$response = $httpClient->request('GET', $base_url.'/api/runs', ['headers' => ['accept' =>'application/json'], 'timeout' => 20]);
				$statusCode = $response->getStatusCode();

			} catch (\Exception $e) {
				$controller->setStatus(1);
				$em->flush();
				$session->getFlashBag()->add(
			        'info',
			        'The controller ('.$controller->getName().') was not reachable... '
			    );
			    continue;
			}

			if ($statusCode != 200) {
				$session->getFlashBag()->add(
			        'info',
			        'The request failed with status code: '.$statusCode
			    );
				
			}

			$runs = $response->toArray();
			foreach ($runs as $r) {
				$ru_count += 1;
				$run = $this->getDoctrine()->getRepository(Run::class)->findOneBy(array('uuid' => $r['uuid'], 'user' => $user->getId()));
				if(is_null($run)){
					$ru_added_count += 1;
					$run = new Run;
					$run->setUuid($r['uuid']);
					$run->setUser($user);
					$run->setLabel($r['label']);
					$run->setDescription($r['description']);
					$run->setStart(new \DateTime($r['start']));
					$run->setDateEnd(new \DateTime($r['dateend']));
					$run->setStatus($r['status']);
					if(!is_null($r['cluster'])){
						$cluster = $this->getDoctrine()->getRepository(Cluster::class)->findOneBy(array('label' => $r['cluster']['label'], 'controller' => $controller));
						$run->setCluster($cluster);
					}
					if(!is_null($r['program'])){
						$program = $this->getDoctrine()->getRepository(Program::class)->findOneBy(array('uuid' => $r['program']['uuid'], 'user' => $user->getId()));
						$run->setProgram($program);
					}
					foreach ($r['steps'] as $s) {
						$step = new RunStep;
						$step->setStart(new \DateTime($s['start']));
						$step->setCommand($s['command']);
						$step->setStatus($s['status']);
						$em->persist($step);
						$run->addRunStep($step);
					}
					$em->persist($run);
				}
			}

    	}

    	$em->flush();

    	$session->getFlashBag()->add(
                    'info',
                    $c_count.' Controller(s) tested, '.$l_count.' lighting(s) founds and '.$l_added_count.' new lighting(s) added. '.$r_count.' recipe(s) founds and '.$r_added_count.' new recipe(s) added. '.$ru_count.' run(s) founds and '.$ru_added_count.' new run(s) added.'
                );

    	return $this->redirectToRoute('home');
    }

     /**
     * @Route("/sync-from-controllers/{id}", name="sync-from-controller")
     */
    public function syncFromController(Request $request, Controller $controller)
    {
    	$session = new Session;

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

    	$em = $this->getDoctrine()->getManager();

    	$l_count = 0; // compteur luminaires totaux
    	$l_added_count = 0; // compteur luminaires ajoutés (nouveaux)

		$httpClient = HttpClient::create(['headers' => [
			    'X-AUTH-TOKEN' => $controller->getAuthToken(),
			]]);
    	$base_url = $controller->getUrl();

    	// Sync Luminaires
		try {
			$response = $httpClient->request('GET', $base_url.'/api/luminaires', ['headers' => ['accept' =>'application/json'], 'timeout' => 20]);
			$statusCode = $response->getStatusCode();

		} catch (\Exception $e) {
			$controller->setStatus(1);
			$em->flush();
			$session->getFlashBag()->add(
		        'info',
		        'The controller ('.$controller->getName().') was not reachable... '
		    );
		    return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
		}

		if ($statusCode != 200) {
			$session->getFlashBag()->add(
		        'info',
		        'The request failed with status code: '.$statusCode
		    );
		    return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
		}

		// On dissocie les luminaires précédemment associés avec ce controller
		$old_luminaires = $controller->getLuminaires();
		foreach ($old_luminaires as $l) {
			$l->setController(null);
			$em->persist($l);
		}
		$em->flush();

		// $contentType = $response->getHeaders()['content-type'][0];
		// $content = $response->getContent();
		$luminaires = $response->toArray();
		foreach ($luminaires as $l) {
			$l_count += 1;

			if(is_null($l['cluster'])){
				// $cluster = $this->getDoctrine()->getRepository(Cluster::class)->findOneBy(array(
				// 	'label' => 1,
				// 	'controller' => $controller,
				// ));
				// if(is_null($cluster)){
				// 	$cluster = new Cluster;
				// 	$cluster->setLabel(1);
				// 	$cluster->setController($controller);
				// 	$em->persist($cluster);
				// 	$em->flush();
				// }
				$cluster = null;
			} else {
				$cluster = $this->getDoctrine()->getRepository(Cluster::class)->findOneBy(array(
					'label' => $l['cluster']['label'],
					'controller' => $controller,
				));
				if(is_null($cluster)){
					$cluster = new Cluster;
					$cluster->setLabel($l['cluster']['label']);
					$cluster->setController($controller);
					$em->persist($cluster);
					$em->flush();
				}
			}

			// Luminaires
			if(is_null($this->getDoctrine()->getRepository(Luminaire::class)->findOneByAddress($l['address']))) {
				$luminaire = new Luminaire;
				$luminaire->setAddress($l['address']);
				$luminaire->setSerial($l['serial']);
				$luminaire->setLigne($l['ligne']);
				$luminaire->setColonne($l['colonne']);
				$luminaire->setController($controller);
				$luminaire->addUser($user);
				$luminaire->setCluster($cluster);
				$em->persist($luminaire);
			// Pcb
				foreach($l['pcbs'] as $p){
					$pcb = new Pcb;
					$pcb->setCrc($p['crc']);
					$pcb->setSerial($p['serial']);
					$pcb->setN($p['n']);
					$pcb->setType($p['type']);
					$em->persist($pcb);
					$luminaire->addPcb($pcb);
				}

			// Channels
				foreach ($l['channels'] as $c) {
					$channel = new Channel;
					$channel->setChannel($c['channel']);
					$channel->setIPeek($c['iPeek']);
					$channel->setLuminaire($luminaire);
					
			// Leds
					# Vérifie que la Led existe dans la base de données, sinon l'ajoute.
                    $led = $this->getDoctrine()->getRepository(Led::class)->findOneBy(array(
                        'wavelength' => $c['led']['wavelength'],
                        'type' => $c['led']['type'],
                        'manufacturer' => $c['led']['manufacturer']));

                    if(is_null($led)) {
                    	$led = new Led;
						$led->setWavelength($c['led']['wavelength']);
						$led->setType($c['led']['type']);
						$led->setManufacturer($c['led']['manufacturer']);
						$em->persist($led);
						$em->flush();
						$channel->setLed($led);
                    } else {
                    	$led->addChannel($channel);
                    }
					$em->persist($channel);
				} // foreach channel

				$l_added_count += 1;
			} else {
				$luminaire = $this->getDoctrine()->getRepository(Luminaire::class)->findOneByAddress($l['address']);
				$luminaire->setController($controller);
				$luminaire->setCluster($cluster);
				$luminaire->setLigne($l['ligne']);
				$luminaire->setColonne($l['colonne']);
				// $em->persist($luminaire);
			}
		}
    	
    	$em->flush();

    	// Sync Logs
		try {
			$response = $httpClient->request('GET', $base_url.'/api/logs', ['headers' => ['accept' =>'application/json'], 'timeout' => 20]);
			$statusCode = $response->getStatusCode();

		} catch (\Exception $e) {
			$controller->setStatus(1);
			$em->flush();
			$session->getFlashBag()->add(
		        'info',
		        'The controller ('.$controller->getName().') was not reachable... '
		    );
		    return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
		}

		if ($statusCode != 200) {
			$session->getFlashBag()->add(
		        'info',
		        'The request failed with status code: '.$statusCode
		    );
			
		}

		$logs = $response->toArray();
		foreach ($logs as $l) {
			$log = $this->getDoctrine()->getRepository(Log::class)->findOneByRemoteId($l['id']);

			if(is_null($log)){
				$log = new Log;
				$log->setRemoteId($l['id']);
				$log->setType($l['type']);
				$log->setValue($l['value']);
				$log->setComment($l['comment']);
				$log->setTime(new \DateTime($l['time']));
				if(!is_null($l['cluster'])){
					$cluster = $this->getDoctrine()->getRepository(Cluster::class)->findOneBy(array(
						'label' => $l['cluster']['label'],
						'controller' => $controller,
					));
					$log->setCluster($cluster);
				}
				if(!is_null($l['luminaire'])){
					$luminaire = $this->getDoctrine()->getRepository(Luminaire::class)->findOneByAddress($l['luminaire']['address']);
					$log->setLuminaire($luminaire);
				}
				$em->persist($log);
			}
		}

		$em->flush();

    	$session->getFlashBag()->add(
                    'info',
                    $controller->getName().' tested, '.$l_count.' lighting(s) founds and '.$l_added_count.' new lighting(s) added.'
                );

    	return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
    }

    /**
     * @Route("/sync-to-controller/{id}", name="sync-to-controller")
     */
    public function syncToController(Request $request, Controller $controller)
    {
    	$session = new Session;

		$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    	$normalizer = new ObjectNormalizer($classMetadataFactory);
		$serializer = new Serializer([$normalizer]);

		$luminaires = $controller->getLuminaires();

		$data = $serializer->normalize($luminaires, null, ['groups' => 'luminaire']);
		// $data = $serializer->serialize($luminaires, 'json', ['groups' => 'luminaire']);

		// $jsonContent = $serializer->serialize($luminaire, 'json');

		// die(print_r($data));	
    	
    	$httpClient = HttpClient::create(['headers' => [
			    'X-AUTH-TOKEN' => $controller->getAuthToken(),
			]]);
    	$base_url = $controller->getUrl();
		$response = $httpClient->request('POST', $base_url.'/update/luminaire', 
			['json' => $data]
		);

		$statusCode = $response->getStatusCode();
		$content = $response->getContent();

		if ($statusCode == 201) {
			$session->getFlashBag()->add(
                'info',
                $controller->getName().' synced. '.$statusCode.' '.$content
            );
		} else {
			$session->getFlashBag()->add(
                'info',
                $controller->getName().' synced. '.$statusCode.' '.$content
            );
		}
		

		

		return $this->redirectToRoute('home');
    }

    /**
     * @Route("/link-controller/{luminaire}:{controller}", name="link-controller")
     */
    public function linkController(Luminaire $luminaire, Controller $controller)
    {
		$session = new Session;

		$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    	$normalizer = new ObjectNormalizer($classMetadataFactory);
		$serializer = new Serializer([$normalizer]);

		$data = $serializer->normalize($luminaire, null, ['groups' => 'luminaire']);

		// dd($data);

		// $jsonContent = $serializer->serialize($luminaire, 'json');

		// die(print_r($data));
    	
    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);    	
    	$base_url = $controller->getUrl();
		$response = $httpClient->request('POST', $base_url.'/remote/luminaire/link', 
			['json' => $data]
		);


		$statusCode = $response->getStatusCode();
		$content = $response->getContent();

		$em = $this->getDoctrine()->getManager();

		if ($statusCode == 200) {

	        $luminaire->setController($controller);
	        $em->persist($luminaire);

			$session->getFlashBag()->add(
                'info',
                'Lighting '.$luminaire->getAddress().' successfully added !'.$content
            );
		} elseif ($statusCode == 200 and $content == "exists") {

			$luminaire->setController($controller);
	        $em->persist($luminaire);

			$session->getFlashBag()->add(
                'info',
                'This lighting already exists in remote controller.'
            );
		} else {
			$session->getFlashBag()->add(
                'info',
                'Something went wrong ! Status code: '.$statusCode.'/'.$content
            );
		}
		
		$em->flush();    	

        return $this->redirectToRoute('view-lightings');
    }

    /**
     * @Route("/unlink-controller/{luminaire}:{controller}", name="unlink-controller")
     */
    public function unlinkController(Luminaire $luminaire, Controller $controller)
    {
    	$session = new Session;
		$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    	$normalizer = new ObjectNormalizer($classMetadataFactory);
		$serializer = new Serializer([$normalizer]);
		$data = $serializer->normalize($luminaire, null, ['groups' => 'luminaire']);
    	
    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);    	
    	$base_url = $controller->getUrl();
		$response = $httpClient->request('POST', $base_url.'/remote/luminaire/unlink', 
			['json' => $data]
		);

		$statusCode = $response->getStatusCode();
		$content = $response->getContent();

		if ($statusCode == 200) {

			$em = $this->getDoctrine()->getManager();
	        $luminaire->setController(null);
	        $em->flush();

			$session->getFlashBag()->add(
                'info',
                'Lighting '.$luminaire->getAddress().' successfully unlinked !'.$content
            );

		} else {
			$session->getFlashBag()->add(
                'info',
                'Something went wrong ! Status code: '.$statusCode.'/'.$content
            );
		}

        return $this->redirectToRoute('view-lightings');
    }

    /**
     * @Route("/set-cluster", name="set-cluster", options={"expose"=true})
     */
    public function setCluster(Request $request)
    {
        $data = $request->get('data');
        $l = $data['l'];
        $c = $data['c'];
        $ctrl = $data['ctrl'];

        $em = $this->getDoctrine()->getManager();
        // $session = new Session();

        $luminaire = $this->getDoctrine()->getRepository(Luminaire::class)->findOneBy(array(
        	'address' => $l,
        	'controller' => $ctrl
        ));
        $cluster = $this->getDoctrine()->getRepository(Cluster::class)->findOneBy(array(
        	'label' => $c,
        	'controller' => $ctrl
        ));

        $controller = $this->getDoctrine()->getRepository(Controller::class)->find($ctrl);
        // Compter les clusters existants
        // $cluster_number = count($this->getDoctrine()->getRepository(Cluster::class)->findByController($ctrl));

        $cluster_added = 0;
        if (is_null($cluster)) {
            $cluster = new Cluster;
            $cluster->setLabel($c);
            // $cluster->addLuminaire($luminaire);
            $cluster->setController($controller);
            $em->persist($cluster);

            // $em->flush();
            // $cluster->addLuminaire($luminaire);
            $luminaire->setCluster($cluster);
            // $cluster_added = 1;
            // $luminaire->setCluster($new_cluster);
            // $em->persist($luminaire);
            $cluster_added = 1;
        } else {
        	// $cluster->addLuminaire($luminaire);
            $luminaire->setCluster($cluster);
            // $em->persist($luminaire);
        }
        
        $em->flush();

        if($cluster_added == 0) {
        	$clusters = $this->getDoctrine()->getRepository(Cluster::class)->findByController($controller);
	        foreach($clusters as $item) {
	            if(count($item->getLuminaires()) == 0) {
	                $em->remove($item);
	                // die(print_r($item->getId()));
	                // $removed = count($item->getLuminaires());
	                // $cluster_added = 1;
	            }
	        }
	        $em->flush();
        }
        
        $cluster_added = 1;

        $session = new Session;

		$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    	$normalizer = new ObjectNormalizer($classMetadataFactory);
		$serializer = new Serializer([$normalizer]);

		$data = $serializer->normalize($luminaire, null, ['groups' => 'luminaire']);
    	
    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $luminaire->getController()->getAuthToken(),
				]]);    	
    	$base_url = $luminaire->getController()->getUrl();
		$response = $httpClient->request('POST', $base_url.'/remote/luminaire/link', 
			['json' => $data]
		);


		$statusCode = $response->getStatusCode();
		$content = $response->getContent();

		$em = $this->getDoctrine()->getManager();

		if ($statusCode == 200) {

	        // $luminaire->setController($controller);
	        // $em->persist($luminaire);

			$session->getFlashBag()->add(
                'info',
                'Lighting '.$luminaire->getAddress().' successfully mapped !'.$content
            );
		} else {
			$session->getFlashBag()->add(
                'info',
                'Something went wrong ! Status code: '.$statusCode.'/'.$content
            );
		}

        $response = new JsonResponse(array(
            'c' => $c,
            'l' => $l,
            'cluster_added' => $cluster_added,
        ));
        return $response;
    }

    /**
     * @Route("/unmap-luminaire/{luminaire}:{controller}", name="unmap-luminaire")
     */
    public function unmapLuminaire(Request $request, Luminaire $luminaire, Controller $controller)
    {
        $em = $this->getDoctrine()->getManager();

        $luminaire->setLigne(NULL);
        $luminaire->setColonne(NULL);

        // $em->persist($luminaire);
        $em->flush();
        
        return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);        
    }

    /**
     * @Route("/set-position", name="set-position", options={"expose"=true})
     */
    public function setPosition(Request $request)
    {
        $data = $request->get('data');
        $id = $data['id'];
        $x = $data['x'];
        $y = $data['y'];
        $ctrl = $data['ctrl'];

        $em = $this->getDoctrine()->getManager();

        $test_luminaire = $this->getDoctrine()->getRepository(Luminaire::class)->getByXY($x,$y, $ctrl);
        $luminaire = $this->getDoctrine()->getRepository(Luminaire::class)->find($id);

        if(is_null($test_luminaire)) {
            $luminaire->setColonne($x);
            $luminaire->setLigne($y);
            // $em->persist($luminaire);
            $em->flush();
        } else {
            $test_luminaire->setColonne(null);
            $test_luminaire->setLigne(null);
            $luminaire->setColonne($x);
            $luminaire->setLigne($y);
            $em->persist($luminaire);
            $em->persist($test_luminaire);
            $em->flush();
        }

        $x_max = $this->getDoctrine()->getRepository(Luminaire::class)->getXMax($ctrl);
        $y_max = $this->getDoctrine()->getRepository(Luminaire::class)->getYMax($ctrl);

        $session = new Session;

		$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    	$normalizer = new ObjectNormalizer($classMetadataFactory);
		$serializer = new Serializer([$normalizer]);

		$data = $serializer->normalize($luminaire, null, ['groups' => 'luminaire']);
    	
    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $luminaire->getController()->getAuthToken(),
				]]);    	
    	$base_url = $luminaire->getController()->getUrl();
		$response = $httpClient->request('POST', $base_url.'/remote/luminaire/link', 
			['json' => $data]
		);


		$statusCode = $response->getStatusCode();
		$content = $response->getContent();

		$em = $this->getDoctrine()->getManager();

		if ($statusCode == 200) {

	        // $luminaire->setController($controller);
	        // $em->persist($luminaire);

			$session->getFlashBag()->add(
                'info',
                'Lighting '.$luminaire->getAddress().' successfully mapped !'.$content
            );
		} else {
			$session->getFlashBag()->add(
                'info',
                'Something went wrong ! Status code: '.$statusCode.'/'.$content
            );
		}
		
		$em->flush(); 


        $response = new JsonResponse(array(
            'id' => $id,
            'x_max' => $x_max['x_max'],
            'y_max' => $y_max['y_max']
        ));
        return $response;
    }

    /**
     * @Route("/lightings/new", name="new-lighting")
     */
    public function newLighting(Request $request)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $em = $this->getDoctrine()->getManager();

        $lighting = new Luminaire;
        $lighting->addUser($user);

        $form = $this->createForm(LuminaireType::class, $lighting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($lighting);
            $em->flush();

            return $this->redirectToRoute('view-lightings');
        }
        
        return $this->render('setup/new-lighting.html.twig', [
            'form' => $form->createView(),
            'edit' => false,
            'navtitle' => 'New Lighting',
        ]);
    }

    /**
     * @Route("/lightings/delete/{id}", name="delete-lighting")
     */
    public function deleteLighting(Request $request, Luminaire $lighting)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $controller = $lighting->getController();

        if(!is_null($controller)){
        	$session = new Session;
			$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
	    	$normalizer = new ObjectNormalizer($classMetadataFactory);
			$serializer = new Serializer([$normalizer]);
			$data = $serializer->normalize($lighting, null, ['groups' => 'luminaire']);
	    	
	    	$httpClient = HttpClient::create(['headers' => [
					    'X-AUTH-TOKEN' => $controller->getAuthToken(),
					]]);    	
	    	$base_url = $controller->getUrl();
			$response = $httpClient->request('POST', $base_url.'/remote/luminaire/unlink', 
				['json' => $data]
			);

			$statusCode = $response->getStatusCode();
			$content = $response->getContent();

			if ($statusCode == 200 || $statusCode == 204) {

				$session->getFlashBag()->add(
	                'info',
	                'Lighting '.$lighting->getAddress().' successfully removed !'.$content
	            );

	            $em = $this->getDoctrine()->getManager();
		        $em->remove($lighting);
		        $em->flush();

			} else {
				$session->getFlashBag()->add(
	                'info',
	                'Something went wrong ! Status code: '.$statusCode.'/'.$content
	            );
			}
        } else {
        	$em = $this->getDoctrine()->getManager();
		    $em->remove($lighting);
		    $em->flush();
        }

        return $this->redirectToRoute('view-lightings');
    }

    /**
     * @Route("/recipes/new", name="new-recipe")
     */
    public function newRecipe(Request $request)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $em = $this->getDoctrine()->getManager();

        $leds = $this->getDoctrine()->getRepository(Led::class)->findAll();

        $recipe = new Recipe;
        $recipe->setTimestamp(time());
        $recipe->setUser($user);
        $uuid = uuid_create(UUID_TYPE_RANDOM);
        $recipe->setUuid($uuid);
        foreach ($leds as $led) {
            $ingredient = new Ingredient;
            $ingredient->setLed($led);
            $em->persist($ingredient);
            $recipe->addIngredient($ingredient);
        }

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recipe);
            $em->flush();

            return $this->redirectToRoute('view-recipes');
        }
        
        return $this->render('setup/new-recipe.html.twig', [
            'form' => $form->createView(),
            'navtitle' => 'New Recipe',
        ]);
    }

    /**
     * @Route("/recipes/edit/{id}", name="edit-recipe")
     */
    public function editRecipe(Request $request, Recipe $recipe)
    {
        $em = $this->getDoctrine()->getManager();

        $recipe->setTimestamp(time());
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // $em->persist($recipe);
            $em->flush();

            return $this->redirectToRoute('view-recipes');
        }
        
        return $this->render('setup/new-recipe.html.twig', [
            'form' => $form->createView(),
            'edit' => true,
            'navtitle' => 'Edit Recipe',
        ]);
    }

    /**
     * @Route("/recipes/delete/{id}", name="delete-recipe")
     */
    public function deleteRecipe(Request $request, Recipe $recipe)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

    	$em = $this->getDoctrine()->getManager();
    	$programs = $recipe->getPrograms();
    	foreach ($programs as $program) {
    		$em->remove($program);
    	}
	    $em->remove($recipe);
	    $em->flush();
        
        return $this->redirectToRoute('view-recipes');
    }

    /**
     * @Route("/play/{cluster}", name="new-play")
     */
    public function newPlay(Request $request, Cluster $cluster)
    {
        $session = new Session;
        $em = $this->getDoctrine()->getManager();

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $controller = $cluster->getController();
        
        # Form to play
        $form = $this->createFormBuilder()
            ->add('recipe', EntityType::class, [
                'class' => Recipe::class,
                'query_builder' => function ($er) use ($user) {
                    return $er->createQueryBuilder('r')
                        ->andWhere('r.user = :user')
                        ->setParameter('user', $user->getId());
                },
                'choice_label' => 'label',
                'choice_value' => 'id', // <--- default IdReader::getIdValue()
            ])
            // ->add('cluster', HiddenType::class, array(
            //     // 'mapped' => false,
            // ))
            ->getForm()
            ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $recipe = $this->getDoctrine()->getRepository(Recipe::class)->find($data['recipe']);

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
	    	$normalizer = new ObjectNormalizer($classMetadataFactory);
			$serializer = new Serializer([$normalizer]);

			$data = $serializer->normalize($recipe, null, ['groups' => 'recipe']);
			$data = array("cluster" => $cluster->getLabel(), "recipe" => $data);

			// die(print_r(json_encode($data)));
	    	
	    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);
    		$base_url = $controller->getUrl();

			try {
				$response = $httpClient->request('POST', $base_url.'/remote/play', 
					['json' => $data]
				);
				$statusCode = $response->getStatusCode();

			} catch (\Exception $e) {
				$session->getFlashBag()->add(
			        'info',
			        'The controller ('.$controller->getName().') was not reachable... '
			    );
			    return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
			}

			if ($statusCode != 200) {
				$session->getFlashBag()->add(
			        'info',
			        'The request failed with status code: '.$statusCode.' '.$response->getContent()
			    );

			    return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
			}

			$session->getFlashBag()->add('info', $response->getContent());

            return $this->redirectToRoute('remote-logs', ['controller' => $controller->getId()]);        
        }

        return $this->render('setup/new-play.html.twig', [
            'form' => $form->createView(),
            'cluster' => $cluster,
            'navtitle' => 'New Play',
        ]);
    }

        /**
     * @Route("/shutdown/{cluster}", name="shutdown")
     */
    public function shutdown(Request $request, Cluster $cluster)
    {
        $session = new Session;
        $em = $this->getDoctrine()->getManager();

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $controller = $cluster->getController();
        
        $data = array("cluster" => $cluster->getLabel());

		// die(print_r(json_encode($data)));
    	
    	$httpClient = HttpClient::create(['headers' => [
			    'X-AUTH-TOKEN' => $controller->getAuthToken(),
			]]);
		$base_url = $controller->getUrl();
		$response = $httpClient->request('POST', $base_url.'/remote/shutdown', 
			['json' => $data]
		);

		$statusCode = $response->getStatusCode();
		$content = $response->getContent();

		$session->getFlashBag()->add(
                'info',
                $content
            );


        return $this->redirectToRoute('remote-logs', ['controller' => $controller->getId()]);        
    }

    /**
     * @Route("/programs/new", name="new-program")
     */
    public function newProgram(Request $request)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $em = $this->getDoctrine()->getManager();

        $program = new Program;
        $uuid = uuid_create(UUID_TYPE_RANDOM);
        $program->setUuid($uuid);
        $program->setUser($user);
        $program->setTimestamp(time());
        $form = $this->createForm(ProgramType::class, $program);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            foreach ($data->getSteps() as $step) {
            	$step->setProgram($program);
            	$em->persist($step);
            }
            $em->persist($program);
            $em->flush();

            return $this->redirectToRoute('view-programs');
        }
        return $this->render('setup/new-program.html.twig', [
            'form' => $form->createView(),
            'edit' => false,
            'navtitle' => 'New Program',
        ]);
    }

    /**
     * @Route("/programs/edit/{id}", name="edit-program")
     */
    public function editProgram(Request $request, Program $program)
    {
        $em = $this->getDoctrine()->getManager();

        $program->setTimestamp(time());
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // $em->persist($recipe);
            $em->flush();

            return $this->redirectToRoute('view-programs');
        }
        
        return $this->render('setup/new-program.html.twig', [
            'form' => $form->createView(),
            'edit' => true,
            'navtitle' => 'Edit Program',
        ]);
    }

    /**
     * @Route("/programs/delete/{id}", name="delete-program")
     */
    public function deleteProgram(Request $request, Program $program)
    {
    	$auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

    	$em = $this->getDoctrine()->getManager();
	    $em->remove($program);
	    $em->flush();
        
        return $this->redirectToRoute('view-programs');
    }

    /**
     * @Route("/run/{cluster}", name="new-run")
     */
    public function newRun(Request $request, Cluster $cluster)
    {
        $session = new Session;
        $em = $this->getDoctrine()->getManager();

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $controller = $cluster->getController();
        
        # Form to play
        $form = $this->createFormBuilder()
            ->add('start', DateTimeType::class, array(
                'data' => new \DateTime("now"),
                'years' => array(2018, 2019, 2020, 2021),
                'minutes' => array(0,5,10,15,20,25,30,35,40,45,50,55)
            ))
            ->add('label')
            ->add('description')
            ->add('program', EntityType::class, [
                'class' => Program::class,
                'query_builder' => function ($er) use ($user) {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.user = :user')
                        ->setParameter('user', $user->getId());
                },
                'choice_label' => 'label',
                'choice_value' => 'id', // <--- default IdReader::getIdValue()
            ])
            // ->add('cluster', HiddenType::class, array(
            //     // 'mapped' => false,
            // ))
            ->getForm()
            ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // dd($data['program']);
            
            $program = $this->getDoctrine()->getRepository(Program::class)->find($data['program']);

	        $uuid = uuid_create(UUID_TYPE_RANDOM);
            $data['uuid'] = $uuid;
	        $now = $data['start'];

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
	    	$normalizer = new ObjectNormalizer($classMetadataFactory);
			$serializer = new Serializer([$normalizer]);

			$pp = $serializer->normalize($program, null, ['groups' => 'program']);
			$dd = array("cluster" => $cluster->getLabel(), "run" => $data, "program" => $pp);

			// dd(json_encode($dd['program']));
	    	
	    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);
    		$base_url = $controller->getUrl();
			$response = $httpClient->request('POST', $base_url.'/remote/run', 
				['json' => $dd]
			);

			$statusCode = $response->getStatusCode();

			if($statusCode == 200){

				// Add Run to remote database
	            $run = new Run;
	        	$run->setUuid($uuid);
		        $run->setStart($now);
		        $run->setLabel($data['label']);
		        $run->setDescription($data['description']);
		        $run->setCluster($cluster);
		        $run->setProgram($program);
		        $run->setUser($user);

		        $em->persist($run);
		        $em->flush();

		        // dd($data);

		        # Fetch lightings addresses
		        $luminaires = $cluster->getLuminaires();
		        $list = " --address ";
		        foreach ($luminaires as $luminaire) {
		            $list .= $luminaire->getAddress()." ";
		        }

		        # Fetch Steps
		        $steps = $program->getSteps();

		        # Start
		        $start = $now;
		        $goto = -1;
		        $step_index = 0;

		        while ($step_index < count($steps)) {
		            $step = $steps[$step_index];
		            $type = $step->getType();

		            switch ($type) {
		                case "time":
		                    list($hours, $minutes) = explode(':', $step->getValue(), 2);
		                    $step_duration = $minutes * 60 + $hours * 3600;
		                    $commands = [];
		                    $ingredients = $step->getRecipe()->getIngredients();
		                    foreach ($ingredients as $ingredient) {
		                        $level = $ingredient->getLevel();
		                        $led = $ingredient->getLed();
		                        $color = $led->getType()."_".$led->getWavelength();
		                        $commands[] = $color." ".$level;
		                    }
		                    $cmd = $this->getParameter('app.velire_cmd').$list." --exclusive --set-power 1 --set-colors ".implode(" ", $commands);
		                    $start = $start->add(new \DateInterval('PT'.$step_duration.'S'));
		                    $new_step = new RunStep();
		                    $new_step->setRun($run);
		                    $new_step->setStart($start);
		                    $new_step->setCommand($cmd);
		                    $new_step->setStatus(0);
		                    $em->persist($new_step);
		                    $em->flush();
		                    $step_index = $step_index + 1;
		                    // die(print_r($start));
		                    break;
		                case "off":
		                    list($hours, $minutes) = explode(':', $step->getValue(), 2);
		                    $step_duration = $minutes * 60 + $hours * 3600;
		                    $cmd = $this->getParameter('app.velire_cmd').$list." --shutdown";
		                    $start = $start->add(new \DateInterval('PT'.$step_duration.'S'));
		                    $new_step = new RunStep();
		                    $new_step->setRun($run);
		                    $new_step->setStart($start);
		                    $new_step->setCommand($cmd);
		                    $new_step->setStatus(0);
		                    $em->persist($new_step);
		                    $em->flush();
		                    $step_index = $step_index + 1;
		                    // die(print_r($cmd));
		                    break;
		                case "goto":
		                    list($s, $n) = explode(':', $step->getValue(), 2);
		                    if($goto < 0){
		                        $goto = $n;
		                    } elseif ($goto == 0) {
		                        $goto = -1;
		                        $step_index = $step_index + 1;
		                    } elseif ($goto > 0) {
		                        $step_index = $s;
		                        $goto = $goto - 1;
		                    }
		                    break;
		            }
		        }

		        $run->setDateEnd($start);
		        $em->persist($run);
		        $em->flush(); 

			}

			$content = $response->getContent();

			$session->getFlashBag()->add(
                    'info',
                    $content
                );

            return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);        
        }

        return $this->render('setup/new-run.html.twig', [
            'form' => $form->createView(),
            'cluster' => $cluster,
            'navtitle' => 'New Play',
        ]);
    }

    /**
     * @Route("/run/delete/{id}", name="delete-run")
     */
    public function deleteRun(Request $request, Run $run)
    {
        $session = new Session;
        $em = $this->getDoctrine()->getManager();

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $controller = $run->getCluster()->getController();


        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
    	$normalizer = new ObjectNormalizer($classMetadataFactory);
		$serializer = new Serializer([$normalizer]);

		$dd = array("uuid" => $run->getUuid());

		// dd(json_encode($dd));
    	
    	$httpClient = HttpClient::create(['headers' => [
			    'X-AUTH-TOKEN' => $controller->getAuthToken(),
			]]);
		$base_url = $controller->getUrl();
		$response = $httpClient->request('POST', $base_url.'/remote/run/delete', 
			['json' => $dd]
		);

		$statusCode = $response->getStatusCode();

		if($statusCode == 200){
			$steps = $run->getRunSteps();
	        foreach ($steps as $step) {
	            $em->remove($step);
	        }
			$em->remove($run);
			$em->flush();
		}

		$content = $response->getContent();

		$session->getFlashBag()->add(
                'info',
                $content
            );

        return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);        

    }

     /**
     * @Route("/remote/logs/{controller}", name="remote-logs")
     */
    public function remoteLogs(Request $request, Controller $controller)
    {
    	$session = new Session;

        $auth_checker = $this->get('security.authorization_checker');
        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

    	$em = $this->getDoctrine()->getManager();

		$httpClient = HttpClient::create(['headers' => [
			    'X-AUTH-TOKEN' => $controller->getAuthToken(),
			]]);
    	$base_url = $controller->getUrl();

    	// Sync Luminaires
		try {
			$response = $httpClient->request('GET', $base_url.'/api/logs', ['headers' => ['accept' =>'application/json'], 'timeout' => 20]);
			$statusCode = $response->getStatusCode();

		} catch (\Exception $e) {
			$controller->setStatus(1);
			$em->flush();
			$session->getFlashBag()->add(
		        'info',
		        'The controller ('.$controller->getName().') was not reachable... '
		    );
		    return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
		}

		if ($statusCode != 200) {
			$session->getFlashBag()->add(
		        'info',
		        'The request failed with status code: '.$statusCode
		    );
		    return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
		}

		// $contentType = $response->getHeaders()['content-type'][0];
		// $content = $response->getContent();
		$remote_logs = $response->toArray();

		foreach ($remote_logs as $remote_log) {
			// dd($remote_log);
			$luminaire = $this->getDoctrine()->getRepository(Luminaire::class)->findOneByAddress($remote_log['value']['address']);
			$test_log = $this->getDoctrine()->getRepository(Log::class)->findOneByControllerLightingTime($controller, $luminaire, date_create($remote_log['time']));
			if(is_null($test_log)){
				// dd('test');
				if (is_null($luminaire)) {
					// dd('emepty luminaire');
					continue;
				}
				$log = new Log;
				$log->setType($remote_log['type']);
				$log->setController($controller);
				$log->setLuminaire($luminaire);
				$log->setCluster($luminaire->getCluster());
				$log->setTime(date_create($remote_log['time']));
				$log->setValue($remote_log['value']);
				$log->setRemoteId($remote_log['id']);
				$em->persist($log);
			}
		}

		$em->flush();

		return $this->redirectToRoute('view-controller', ['id' => $controller->getId()]);
	}

}
