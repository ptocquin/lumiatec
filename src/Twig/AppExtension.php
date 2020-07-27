<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('routeExists', [$this, 'routeExists']),
        ];
    }

    public function calculateArea(int $width, int $length)
    {
        return $width * $length;
    }

    function routeExists($name)
    {
        // I assume that you have a link to the container in your twig extension class
        //$router = $this->container->get('router');
        //return (null === $router->getRouteCollection()->get($name)) ? false : true;

        return null !== $router->getRouteCollection()->get($name);
    }
}