<?php

namespace Drutiny\Report;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class FormatFactory
{
    use ContainerAwareTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function create(string $format, array $options = [])
    {
        foreach ($this->container->findTaggedServiceIds('format') as $id => $info) {
            $formatter = $this->container->get($id);
            if ($formatter->getName() != $format) {
                continue;
            }

            $formatter->setOptions($options);
            return $formatter;
        }
        throw new \InvalidArgumentException("Reporting format '$format' is not supported.");
    }
}
