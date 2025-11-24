<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/packages/*.yaml', 'glob');
        $loader->load($confDir.'/packages/'.$this->environment.'/*.yaml', 'glob');

        $loader->load($confDir.'/services.yaml');
        if (file_exists($confDir.'/services_'.$this->environment.'.yaml')) {
            $loader->load($confDir.'/services_'.$this->environment.'.yaml');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/routes/*.yaml');
        if (is_dir($confDir.'/routes/'.$this->environment)) {
            $routes->import($confDir.'/routes/'.$this->environment.'/*.yaml');
        }
        if (file_exists($confDir.'/routes.yaml')) {
            $routes->import($confDir.'/routes.yaml');
        }
    }
}
