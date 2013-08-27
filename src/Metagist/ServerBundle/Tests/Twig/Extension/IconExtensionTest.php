<?php
namespace Metagist\ServerBundle\Tests\Twig\Extension;

use Metagist\ServerBundle\Twig\Extension\IconExtension;

/**
 * Tests the twig extension to obtain icons for category groups.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class IconExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var IconExtension
     */
    private $extension;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->extension = new IconExtension(array('test' => 'icon-test'));
    }
    
    /**
     * Test the usable methods.
     */
    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertNotEmpty($functions);
        $this->assertArrayHasKey('icon', $functions);
        $this->assertArrayHasKey('stars', $functions);
        $this->assertArrayHasKey('symbols', $functions);
    }
    
    /**
     * Test the retrieval of a twitter bootstrap icon for a key.
     * 
     */
    public function testIcon() 
    {
        $icon = $this->extension->icon('test');
        $this->assertEquals('<i class="icon-test"></i>', $icon);
    }
    
    /**
     * Ensures an empty string is returned.
     */
    public function testIconWithUnknownKey() 
    {
        $icon = $this->extension->icon('asd');
        $this->assertEquals('', $icon);
    }
    
    /**
     * Ensures the stars() method returns the correct number of stars.
     */
    public function testStars()
    {
        $result = $this->extension->stars(5);
        $icon = '<i class="symbol icon icon-star"></i>';
        $this->assertEquals(str_repeat($icon, 5), $result);
    }
    
    /**
     * Ensures the package type is represented as symbol
     */
    public function testSymbolsWithLibrary()
    {
        $package = new \Metagist\ServerBundle\Entity\Package('test/test');
        $package->setType('library');
        $this->assertContains('icon-wrench', $this->extension->symbols($package));
    }
    
    /**
     * Ensures magnification is regarded.
     */
    public function testSymbolsWithMagnification()
    {
        $package = new \Metagist\ServerBundle\Entity\Package('test/test');
        $package->setType('library');
        $this->assertContains('icon-2x', $this->extension->symbols($package, 2));
    }
    
    /**
     * Ensures metainfos are traversed and checked agains specs.
     */
    public function testSymbolsWithMetainfos()
    {
        $package = new \Metagist\ServerBundle\Entity\Package('test/test');
        $package->setMetaInfos(array(\Metagist\ServerBundle\Entity\Metainfo::fromValue('featured', true)));
        
        $this->assertContains('icon-volume-up', $this->extension->symbols($package));
    }
    
    /**
     * Test the name of the extension.
     */
    public function testGetName()
    {
        $this->assertEquals('metagist_icons', $this->extension->getName());
    }
}
