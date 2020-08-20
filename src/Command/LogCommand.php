<?php

// src/Command/ExampleCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Controller;
use App\Entity\Log;
use App\Entity\Luminaire;



use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\Lumiatec;



// 1. Import the ORM EntityManager Interface
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class LogCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:log';
    
    // 2. Expose the EntityManager in the class level
    private $entityManager;
    private $lumiatec;

    public function __construct(EntityManagerInterface $entityManager, Lumiatec $lumiatec)
    {
        
        // 3. Update the value of the private entityManager variable through injection
        $this->entityManager = $entityManager;
        $this->lumiatec = $lumiatec;

        parent::__construct();
    }
    
    protected function configure()
    {
        // ...
    }

    // 4. Use the entity manager in the command code ...
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->entityManager;

        // $time = date('Y-m-d H:i:s');
        // $time = date('2020-07-10 17:42:00');
        // $output->writeln(date('Y-m-d H:i:s'));

        $log_repo = $em->getRepository(Log::class);
        $controller_repo = $em->getRepository(Controller::class);
        
        $controllers = $controller_repo->findAll();

        foreach ($controllers as $controller) {
            $output->writeln(date('Y-m-d H:i:s'));
            $last_log = $em->getRepository(Log::class)->getControllerLastLog($controller);
            if(is_null($last_log)) {
                $date = "1970-01-01 00:00:00";
            } else {
                $date = date_format($last_log->getTime(),"Y-m-d H:i:s");
            }
            $result = $this->lumiatec->postToControllerAPI($controller, '/remote/logs', array("date" => $date));
            foreach ($result['messages'] as $message) {
                $output->writeln($message);
            }
            
            $remote_logs = json_decode($result['content'], true);
            if(empty($remote_logs)){
                continue;
            }
            foreach ($remote_logs as $remote_log) {
                // $output->writeln($remote_log['time']['date']);
                $luminaire = $em->getRepository(Luminaire::class)->findOneByAddress($remote_log['value']['address']);
                $test_log = $em->getRepository(Log::class)->findByControllerLightingTime($controller, $luminaire, date_create($remote_log['time']['date']));

                // dd($test_log);
                if(empty($test_log)){
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
                    $log->setTime(date_create($remote_log['time']['date']));
                    $log->setValue($remote_log['value']);
                    $log->setRemoteId($remote_log['id']);
                    $em->persist($log);
                }
            }
            $em->flush();
        }

        
         
        return 0;
    }
}