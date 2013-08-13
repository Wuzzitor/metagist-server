<?php
namespace Metagist\ServerBundle\Tests\Twig\Extension;

use Metagist\ServerBundle\Twig\Extension\MetaInfosExtension;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Tests the twig extension to render metainfo collections (of the same group).
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfosExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfosExtension
     */
    private $extension;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $filter = function(\Doctrine\Common\Collections\Collection $collection) {
            return $this->getFirst($collection);
        };
        
        $this->extension = new MetaInfosExtension(
            array(
                'test/list' => array('displayAs' => 'url'),
                'test/badge' => array('displayAs' => 'badge'),
                'test/styled' => array('class' => 'label'),
                'test/filter' => array('filter' => $filter),
            )
        );
    }
    
    /**
     * Test the usable methods.
     */
    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertNotEmpty($functions);
        $this->assertArrayHasKey('renderInfos', $functions);
        $this->assertArrayHasKey('renderInfo', $functions);
    }
    
    /**
     * Ensures a collection is rendered as list.
     */
    public function testRenderAsDefault() 
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $collection->add(Metainfo::fromValue('test/unknown', 'http://an.url'));
        $collection->add(Metainfo::fromValue('test/unknown', 'http://an.other.url'));
        
        $list = $this->extension->renderInfos($collection);
        $this->assertContains('<li><span>http://an.url</span></li>', $list);
        $this->assertContains('<li><span>http://an.other.url</span></li>', $list);
    }
    
    /**
     * Ensures that css classes are applied if given.
     */
    public function testApplyClass() 
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $collection->add(Metainfo::fromValue('test/styled', 'http://an.url'));
        
        $list = $this->extension->renderInfos($collection);
        $this->assertContains('<li><span class="label">', $list);
    }
    
    /**
     * Tests the url rendering
     */
    public function testDisplayAsUrl() 
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $collection->add(Metainfo::fromValue('test/list', 'http://an.url'));
        
        $list = $this->extension->renderInfos($collection);
        $this->assertContains('<li><span><a href="http://an.url" target="_blank">http://an.url</a></span></li>', $list);
    }
    
    /**
     * Tests the url rendering
     */
    public function testDisplayAsBadge() 
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $collection->add(Metainfo::fromValue('test/badge', 'http://an.url'));
        
        $list = $this->extension->renderInfos($collection);
        $this->assertContains('<li><span><img src="http://an.url" alt="badge for ', $list);
    }
    
    /**
     * Ensures a given filter is used.
     */
    public function testWithFilter()
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        $collection->add(Metainfo::fromValue('test/filter', 'http://an.url'));
        $collection->add(Metainfo::fromValue('test/filter', 'http://an.other.url'));
        
        $list = $this->extension->renderInfos($collection);
        $this->assertContains('http://an.url', $list);
        $this->assertNotContains('http://an.other.url', $list);
    }
    
    /**
     * Test filter.
     * 
     * @param \Doctrine\Common\Collections\Collection $info
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFirst(\Doctrine\Common\Collections\Collection $info)
    {
        return new \Doctrine\Common\Collections\ArrayCollection(array($info->first()));
    }
    
    /**
     * Test the name of the extension.
     */
    public function testGetName()
    {
        $this->assertEquals(MetaInfosExtension::NAME, $this->extension->getName());
    }
    
    /**
     * Ensures a single metainfo can in rendered.
     */
    public function testRenderMetaInfo()
    {
        $metaInfo = Metainfo::fromValue('test/url', 'http://an.url');
        $result = $this->extension->renderInfo($metaInfo);
        $this->assertContains('<span>http://an.url</span>', $result);
    }
}
