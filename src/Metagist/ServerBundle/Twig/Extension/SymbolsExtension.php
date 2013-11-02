<?php
namespace Metagist\ServerBundle\Twig\Extension;

use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Twig extension to create icons.
 * 
 * To be used with twitter bootstrap.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class SymbolsExtension extends \Twig_Extension
{
    /**
     * "Tests"
     * @var array
     */
    private $specs = array();
    
    /**
     * Init with the mapping to use.
     * 
     * @param array $mapping
     */
    public function __construct()
    {
        $this->specs = array(
            'maintainers' => array(
                'group'    => 'maintainers',
                'callback' => function ($value) { return ($value > 1); },
                'icon'     => 'group',
                'title'    => 'This package is maintained by more than one person.',
                'class'    => 'text-success'
            ),
            'one_maintainer' => array(
                'group'    => 'maintainers',
                'callback' => function ($value) { return ($value == 1); },
                'icon'     => 'meh-o',
                'title'    => 'This package is only maintained by one person.',
                'class'    => 'text-warning'
            ),
            Metainfo::STARGAZERS => array(
                'group'    => Metainfo::STARGAZERS, 
                'callback' => function ($value) { return true; },
                'icon'     => 'star',
                'title'    => '%s stargazers',
                'class'    => 'text-primary badge'
            )
        );
    }
    
    /**
     * Returns the usable methods.
     * 
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'symbols' => new \Twig_Function_Method($this, 'symbols', array("is_safe" => array("html"))),
        );
    }
    
    /**
     * Returns the symbols for a package
     * 
     * @param Package $package
     * @param int     $magnification
     * @return string
     */
    public function symbols(Package $package, $magnification = 1)
    {
        $symbols = array();
        
        if ($package->getOverallRating() >= 4) {
            $symbols['star'] = 'This package has a high rating.';
        }
        
        //metainfos
        if (($metainfos = $package->getMetaInfos()) !== null) {
            foreach ($this->specs as $spec => $data) {
                if ($this->metainfosProvide($metainfos, $data)) {
                    $symbols[$data['icon']] = $data;
                }
            }
        }
        
        $buffer = '';
        foreach ($symbols as $icon => $display) {
            if (is_string($display)) {
                $class = '';
                $title = $display;
            } else {
                $title = $display['title'];
                $icon  = $display['icon'];
                $class = @$display['class'];
            }
            
            $buffer .= $this->renderIcon($icon, $title, $magnification, $class);
        }
        return $buffer;
    }
    
    /**
     * Renders a twbs icon.
     * 
     * @param string $icon
     * @param string $title
     * @param int    $magnification
     * @param string $class
     * @return string
     */
    private function renderIcon($icon, $title, $magnification = 1, $class = '')
    {
        return '<i class="symbol fa fa-' . $icon 
            . ' fa-' . $magnification . 'x ' 
            . $class 
            . '" title="' . $title . '"></i>';
    }
    
    /**
     * Check that a group info provides the required value.
     * 
     * @param array  $metainfos
     * @param array $data spec data
     * 
     * @return boolean
     */
    private function metainfosProvide($metainfos, array $data)
    {
        foreach ($metainfos as $metainfo) {
            /* @var $metainfo \Metagist\ServerBundle\Entity\Metainfo */
            if ($metainfo->getGroup() == $data['group']) {
                $callback = $data['callback'];
                return $callback($metainfo->getValue());
            }
        }
    }
    
    /**
     * Returns the name of the extension.
     * 
     * @return string
     */
    public function getName()
    {
        return 'metagist_symbols';
    }
}
