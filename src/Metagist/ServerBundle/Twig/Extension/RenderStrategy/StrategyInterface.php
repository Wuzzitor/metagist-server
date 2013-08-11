<?php
namespace Metagist\ServerBundle\Twig\Extension\RenderStrategy;

/**
 * Interface for classes implementing a metainfo render strategy.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
interface StrategyInterface
{
    /**
     * Renders a single metainfo.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @return string
     */
    public function render(\Metagist\MetaInfo $metaInfo);
}