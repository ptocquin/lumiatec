<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Routing\RouterInterface;


class AppExtension extends AbstractExtension
{
    public function __construct(RouterInterface $router) 
    {
        $this->router = $router;
    }

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
        // $router = $this->container->get('router');
        //return (null === $router->getRouteCollection()->get($name)) ? false : true;

        // return null !== $env->getExtension('routing')->getPath($name);
        return null !== $this->router->getRouteCollection()->get($name);
    }
}