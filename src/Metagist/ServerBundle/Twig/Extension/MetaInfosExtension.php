<?php
namespace Metagist\ServerBundle\Twig\Extension;

use Metagist\ServerBundle\Entity\Metainfo;
use Metagist\ServerBundle\Twig\Extension\RenderStrategy\StrategyInterface;

/**
 * Twig extension to render a  metainfo.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfosExtension extends \Twig_Extension
{
    /**
     * "category/group" indentifier => render strategy
     * 
     * @var StrategyInterface[]
     */
    private $strategies = array();
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->strategies = array(
            Metainfo::PACKAGIST_DOWNLOADS   => new RenderStrategy\TextBadge(),
            Metainfo::PACKAGIST_FAVERS      => new RenderStrategy\TextBadge(),
            Metainfo::STARGAZERS      => new RenderStrategy\TextBadge(),
            Metainfo::OPEN_ISSUES      => new RenderStrategy\TextBadge(),
            Metainfo::MAINTAINERS  => new RenderStrategy\TextBadge(),
            Metainfo::STILL_MAINTAINED  => new RenderStrategy\Badge(),
        );
    }
    
    /**
     * Returns the names of the provided functions.
     * 
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'renderInfo' => new \Twig_Function_Method($this, 'renderInfo', array("is_safe" => array("html"))),
        );
    }
    
    /**
     * Returns the name of the extension.
     * 
     * @return string
     */
    public function getName()
    {
        return 'metainfos_extension';
    }
    
    /**
     * Render a single metainfo.
     * 
     * @param MetaInfo $metaInfo
     * @return string
     */
    public function renderInfo(Metainfo $metaInfo)
    {
        $group    = $metaInfo->getGroup();
        if (!isset($this->strategies[$group])) {
            return '';
        }
        return $this->renderMetaInfo($metaInfo, $this->strategies[$group]);
    }
    
    /**
     * Renders a single metainfo.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @param array $config
     * @return string
     */
    protected function renderMetaInfo(Metainfo $metaInfo, StrategyInterface $strategy)
    {
        return $strategy->render($metaInfo);
    }
}