<?php
namespace Metagist\ServerBundle\Twig\Extension\RenderStrategy;

use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Strategy to render a twbs text badge
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class TextBadge implements StrategyInterface
{
    /**
     * Renders the metainfo value as badge / image.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @return string
     */
    public function render(Metainfo $metaInfo)
    {
        $template = '<span class="badge">%s %s</span>';
        return sprintf(
            $template,
            $metaInfo->getValue(),
            $metaInfo->getGroup()
        );
    }

}