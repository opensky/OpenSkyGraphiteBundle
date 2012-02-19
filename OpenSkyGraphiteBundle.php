<?php

namespace OpenSky\Bundle\GraphiteBundle;

use OpenSky\Bundle\GraphiteBundle\DependencyInjection\Compiler\EventDispatcherOverridePass;
use OpenSky\Bundle\GraphiteBundle\DependencyInjection\OpenSkyGraphiteExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenSkyGraphiteBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new OpenSkyGraphiteExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventDispatcherOverridePass());
    }
}
