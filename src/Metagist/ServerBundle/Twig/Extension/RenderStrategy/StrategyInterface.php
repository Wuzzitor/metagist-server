<?php
namespace Metagist\ServerBundle\Twig\Extension\RenderStrategy;

use Metainfo;

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
     * @param Metainfo $metaInfo
     * @return string
     */
    public function render(Metainfo $metaInfo);
}