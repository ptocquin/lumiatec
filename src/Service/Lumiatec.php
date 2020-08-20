<?php
// src/Service/Lumiatec.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

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

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Luminaire;
use App\Entity\Pcb;
use App\Entity\Channel;
use App\Entity\Led;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\Program;
use App\Entity\Step;
use App\Entity\Cluster;
use App\Entity\Run;
use App\Entity\RunStep;

class Lumiatec
{
    private $params;
    private $em;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em)
    {
        $this->params = $params;
        $this->em = $em;
    }

    public function postToControllerAPI($controller, $uri, $data)
    {
	    $httpClient = HttpClient::create(['headers' => [
			    'X-AUTH-TOKEN' => $controller->getAuthToken(),
			]]);
    	$base_url = $controller->getUrl();
    	$messages = array();
    	$output = array('status' => null, 'content' => null, 'messages' => array());

		try {
			$response = $httpClient->request('POST', $base_url.$uri, ['json' => $data]);
			$statusCode = $response->getStatusCode();

		} catch (\Exception $e) {
			$controller->setStatus(1);
			$this->em->flush();
			$output['messages'][] = array('type'=> 'danger', 'short' => 'Connexion error !', 'message' => 'The controller '.$controller->getName().' was not responding... ');
			return $output;
		}

		$controller->setStatus(0);
		$this->em->flush();
		$output['status'] = $statusCode;

		if ($statusCode != 200) {
			$output['messages'][] = array('type' => 'danger', 'short' => 'Connexion error !', 'message' => 'Something went wrong with the controller '.$controller->getName().'. It responds with code '.$statusCode);
			return $output;			
		}

		$output['content'] = $response->getContent();
		$output['messages'][] = array('type' => 'info', 'short' => 'Connexion successful !', 'message' => 'The controller ('.$controller->getName().') was successfuly connected... ');
		return  $output;
    }

    public function getFromControllerAPI($controller, $uri)
    {
	    $httpClient = HttpClient::create(['headers' => [
			    'X-AUTH-TOKEN' => $controller->getAuthToken(),
			]]);
    	$base_url = $controller->getUrl();
    	$messages = array();
    	$output = array('status' => null, 'content' => null, 'messages' => array());

		try {
			$response = $httpClient->request('GET', $base_url.$uri, ['headers' => ['accept' =>'application/json'], 'timeout' => 2]);
			$statusCode = $response->getStatusCode();

		} catch (\Exception $e) {
			$controller->setStatus(1);
			$this->em->flush();
			$output['messages'][] = array('type'=> 'danger', 'short' => 'Connexion error !', 'message' => 'The controller '.$controller->getName().' was not responding... ');
			return $output;
		}

		$controller->setStatus(0);
		$this->em->flush();
		$output['status'] = $statusCode;

		if ($statusCode != 200) {
			$output['messages'][] = array('type' => 'danger', 'short' => 'Connexion error !', 'message' => 'Something went wrong with the controller '.$controller->getName().'. It responds with code '.$statusCode);
			return $output;			
		}

		$output['content'] = $response->getContent();
		$output['messages'][] = array('type' => 'info', 'short' => 'Connexion successful !', 'message' => 'The controller ('.$controller->getName().') was successfuly connected... ');
		return  $output;
    }

    public function setLuminaire($data, $controller, $cluster, $user, $luminaire=null)
    {
    	if(is_null($luminaire)) {
    		$luminaire = new Luminaire;
    		$luminaire->setAddress($data['address']);
			$luminaire->setSerial($data['serial']);
			$luminaire->setLigne($data['ligne']);
			$luminaire->setColonne($data['colonne']);
			$luminaire->setController($controller);
			$luminaire->addUser($user);
			$luminaire->setCluster($cluster);
			$this->em->persist($luminaire);

			// Pcb
			foreach($data['pcbs'] as $p){
				$pcb = new Pcb;
				$pcb->setCrc($p['crc']);
				$pcb->setSerial($p['serial']);
				$pcb->setN($p['n']);
				$pcb->setType($p['type']);
				$this->em->persist($pcb);
				$luminaire->addPcb($pcb);
			}

			// Channels
			foreach ($data['channels'] as $c) {
				$channel = new Channel;
				$channel->setChannel($c['channel']);
				$channel->setIPeek($c['iPeek']);
				$channel->setLuminaire($luminaire);
				
			// Leds
				# Vérifie que la Led existe dans la base de données, sinon l'ajoute.
	            $led = $this->em->getRepository(Led::class)->findOneBy(array(
	                'wavelength' => $c['led']['wavelength'],
	                'type' => $c['led']['type'],
	                'manufacturer' => $c['led']['manufacturer']));

	            if(is_null($led)) {
	            	$led = new Led;
					$led->setWavelength($c['led']['wavelength']);
					$led->setType($c['led']['type']);
					$led->setManufacturer($c['led']['manufacturer']);
					$this->em->persist($led);
					$this->em->flush();
					$channel->setLed($led);
	            } else {
	            	$led->addChannel($channel);
	            }
				$this->em->persist($channel);
			} // foreach channel
    	} else {
    		$luminaire->setAddress($data['address']);
			$luminaire->setSerial($data['serial']);
			$luminaire->setLigne($data['ligne']);
			$luminaire->setColonne($data['colonne']);
			$luminaire->setController($controller);
			$luminaire->addUser($user);
			$luminaire->setCluster($cluster);
		}

		$this->em->flush();
    }

    public function setRecipe($data, $controller, $user, $recipe=null)
    {
    	$output = array('status' => null, 'content' => null, 'messages' => array());
    	if(!is_null($data)) {
	    	$flag = false; // Update
	    	if(is_null($recipe)) {
	    		$flag = true; // Create
	    		$recipe = new Recipe;
	    		$recipe->setUuid($data['uuid']);
	    		$recipe->setUser($user);
	    		$recipe->setTimestamp($data['timestamp']);
	    	} elseif ($recipe->getTimestamp() <= $data['timestamp']) {
	    		$output['content'] = 'exists';
				return $output;
	    	}
			if($recipe->getTimestamp() <= $data['timestamp']) { // Nouvelle recette ou à updater à partir du contrôleur
	    		$recipe->setLabel($data['label']);
	        	$recipe->setDescription($data['description']);

	        	if(!$flag){
		        	foreach ($recipe->getIngredients() as $ingredient) {
						$this->em->remove($ingredient);
					}
	        	}

		        foreach ($data['ingredients'] as $i) {
		            $led = $this->em->getRepository(Led::class)->findOneBy(
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
		                $this->em->persist($led);
		            }

		            $ingredient = new Ingredient;
		            $ingredient->setLed($led);
		            $ingredient->setLevel($i['level']);
		            $ingredient->setPwmStart($i['pwm_start']);
		            $ingredient->setPwmStop($i['pwm_stop']);
		            $this->em->persist($ingredient);
		            $recipe->addIngredient($ingredient);
		        }
		        if($flag) {
		        	$this->em->persist($recipe);
		        }
		        $this->em->flush();

				$output['messages'][] = array('type' => 'info', 'short' => 'Connexion successful !', 'message' => 'Recipe '.$recipe->getLabel().' was successfuly retrieved from the controller ('.$controller->getName().').');
				return  $output;
			}
		} elseif(!is_null($recipe)) {	// Envoyer la recette plus récente sur le contrôleur
		    	$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
		    	$normalizer = new ObjectNormalizer($classMetadataFactory);
				$serializer = new Serializer([$normalizer]);

				$data = $serializer->normalize($recipe, null, ['groups' => 'recipe']);
				$data = array("recipe" => $data);

				$messages = array();
		    	$output = array('status' => null, 'content' => null, 'messages' => array());
		    	
		    	$httpClient = HttpClient::create(['headers' => [
					    'X-AUTH-TOKEN' => $controller->getAuthToken(),
					]]);
				$base_url = $controller->getUrl();

				try {
					$response = $httpClient->request('POST', $base_url.'/remote/update/recipe', 
						['json' => $data]
					);
					$statusCode = $response->getStatusCode();
				} catch (\Exception $e) {
					$controller->setStatus(1);
					$this->em->flush();
					$output['messages'][] = array('type'=> 'danger', 'short' => 'Connexion error !', 'message' => 'The controller '.$controller->getName().' was not responding... ');
					return $output;		}

				$controller->setStatus(0);
				$this->em->flush();
				$output['status'] = $statusCode;
				if ($statusCode != 200) {
					$output['messages'][] = array('type' => 'danger', 'short' => 'Connexion error !', 'message' => 'Something went wrong with the controller '.$controller->getName().' when updating recipes. It responded with code '.$statusCode);
					return $output;			
				}

				$output['content'] = $response->getContent();
				$output['messages'][] = array('type' => 'info', 'short' => 'Connexion successful !', 'message' => 'Recipe '.$recipe->getLabel().' was successfuly sent to the controller '.$controller->getName().'.');
				return  $output;
		}
	}

    public function setProgram($data, $controller, $user, $program=null)
    {
    	$output = array('status' => null, 'content' => null, 'messages' => array());
    	if(!is_null($data)){
    		$flag = false; // Update
	    	if(is_null($program)) { // N'existe pas sur contrôleur
	    		$flag = true; // Create
				$program = new Program;
				$program->setUuid($data['uuid']);
				$program->setUser($user);
				$program->setTimestamp($data['timestamp']);
			} elseif ($program->getTimestamp() == $data['timestamp']) {
				$output['content'] = 'exists';
				return $output;
			}
			if($program->getTimestamp() <= $data['timestamp']) {
				$program->setLabel($data['label']);
				$program->setDescription($data['description']);
				$program->setTimestamp($data['timestamp']);

				if(!$flag) {
					foreach ($program->getSteps() as $step) {
						$this->em->remove($step);
					}
				}
				
				foreach ($data['steps'] as $s) {
					$step = new Step;
					$step->setType($s['type']);
					$step->setRank($s['rank']);
					$step->setValue($s['value']);
					if(!is_null($s['recipe'])){
						$recipe = $this->em->getRepository(Recipe::class)->findOneBy(array('uuid' => $s['recipe']['uuid'], 'user' => $user->getId()));
						$step->setRecipe($recipe);
					}
					$this->em->persist($step);
					$program->addStep($step);
				}

				if($flag) {
					$this->em->persist($program);
				}
			}
			$this->em->flush();
			$output['messages'][] = array('type' => 'info', 'short' => 'Connexion successful !', 'message' => 'Program '.$program->getLabel().' was successfuly retrieved from the controller '.$controller->getName().'.');
			return $output;
    	} elseif(!is_null($program)) {
			$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
	    	$normalizer = new ObjectNormalizer($classMetadataFactory);
			$serializer = new Serializer([$normalizer]);

			$data = $serializer->normalize($program, null, ['groups' => 'program']);
			$data = array("program" => $data);

	    	$httpClient = HttpClient::create(['headers' => [
				    'X-AUTH-TOKEN' => $controller->getAuthToken(),
				]]);
    		$base_url = $controller->getUrl();

    		try {
				$response = $httpClient->request('POST', $base_url.'/remote/update/program', 
					['json' => $data]
				);
				$statusCode = $response->getStatusCode();
			} catch (\Exception $e) {
				$controller->setStatus(1);
				$this->em->flush();
				$output['messages'][] = array('type'=> 'danger', 'short' => 'Connexion error !', 'message' => 'The controller '.$controller->getName().' was not responding... ');
				return $output;
			}

			$controller->setStatus(0);
			$this->em->flush();
			$output['status'] = $statusCode;
			if ($statusCode != 200) {
				$output['messages'][] = array('type' => 'danger', 'short' => 'Connexion error !', 'message' => 'Something went wrong with the controller '.$controller->getName().' when updating programs. It responded with code '.$statusCode);
				return $output;			
			}

			$output['content'] = $response->getContent();
			if($output['content'] !== 'exists') {
				$output['messages'][] = array('type' => 'info', 'short' => 'Connexion successful !', 'message' => 'Program '.$program->getLabel().' was successfuly '.$output['content'].' on the controller '.$controller->getName().'.');
			}
			return  $output;
		}
	}

    public function setRun($data, $controller, $user, $run=null)
    {
    	$output = array('status' => null, 'content' => null, 'messages' => array());
    	$flag = false; // Update
    	if(is_null($run)) {
    		$flag = true; // Create
			$run = new Run;
			$run->setUuid($data['uuid']);
			$run->setUser($user);
			$run->setTimestamp($data['timestamp']);
		} 

		if($run->getTimestamp() <= $data['timestamp']) { // Nouveau run ou à updater à partir du contrôleur
    		$run->setLabel($data['label']);
			$run->setDescription($data['description']);
			$run->setStart(new \DateTime($data['start']));
			$run->setDateEnd(new \DateTime($data['dateend']));
			$run->setStatus($data['status']);

        	if(!$flag){
	        	foreach ($run->getRunSteps() as $step) {
					$this->em->remove($step);
				}
        	}

			if(!is_null($data['cluster'])){
				$cluster = $this->em->getRepository(Cluster::class)->findOneBy(array('label' => $data['cluster']['label'], 'controller' => $controller));
				$run->setCluster($cluster);
			}
			if(!is_null($data['program'])){
				$program = $this->em->getRepository(Program::class)->findOneBy(array('uuid' => $data['program']['uuid'], 'user' => $user->getId()));
				$run->setProgram($program);
			}
			foreach ($data['steps'] as $s) {
				$step = new RunStep;
				$step->setStart(new \DateTime($s['start']));
				$step->setCommand($s['command']);
				$step->setStatus($s['status']);
				$this->em->persist($step);
				$run->addRunStep($step);
			}

			if($flag){
				$this->em->persist($run);
			}
			
			$this->em->flush();
		} else {

		}	

	}

    public function updateLogs()
    {

    }
}