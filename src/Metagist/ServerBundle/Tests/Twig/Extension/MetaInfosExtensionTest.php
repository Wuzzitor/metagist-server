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
        $this->extension = new MetaInfosExtension();
    }
    
    /**
     * Test the usable methods.
     */
    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertNotEmpty($functions);
        $this->assertArrayHasKey('renderInfo', $functions);
    }
    
    /**
     * Tests the url rendering
     */
    public function testDisplayAsUrl() 
    {
        $info = Metainfo::fromValue(Metainfo::HOMEPAGE, 'http://an.url');
        
        $list = $this->extension->renderInfo($info);
        $this->assertContains('<a href="http://an.url" target="_blank">http://an.url</a>', $list);
    }
    
    /**
     * Tests the url rendering
     */
    public function testDisplayAsBadge() 
    {
        $res = $this->extension->renderInfo(Metainfo::fromValue(Metainfo::MAINTAINERS, 3));
        $this->assertContains('<li><span><img src="http://an.url" alt="badge for ', $res);
    }
    
    /**
     * Tests the url rendering
     */
    public function testDisplayAsTextBadge() 
    {
        $res = $this->extension->renderInfo(Metainfo::fromValue(Metainfo::MAINTAINERS, 3));
        $this->assertContains('<span class="badge">3 maintainers</span>', $res);
    }
    
    public function testDoesNotRenderWithoutStrategy()
    {
        $metaInfo = Metainfo::fromValue('test/url', 'http://an.url');
        $result = $this->extension->renderInfo($metaInfo);
        $this->assertEmpty($result);
    }
    
    /**
     * Test the name of the extension.
     */
    public function testGetName()
    {
        $this->assertEquals('metainfos_extension', $this->extension->getName());
    }
    
    
}
