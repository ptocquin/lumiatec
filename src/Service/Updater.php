<?php
// src/Service/Updater.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;


class Updater
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }


    public function updateRecipe()
    {

    }
}