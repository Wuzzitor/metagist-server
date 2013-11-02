<?php
namespace Metagist\ServerBundle\Tests\Twig\Extension;

use Metagist\ServerBundle\Twig\Extension\SymbolsExtension;

/**
 * Tests the twig extension to obtain symbols for category groups.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class SymbolsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var SymbolsExtension
     */
    private $extension;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->extension = new SymbolsExtension();
    }
    
    /**
     * Test the usable methods.
     */
    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertNotEmpty($functions);
        $this->assertArrayHasKey('symbols', $functions);
    }
    
    /**
     * Ensures magnification is regarded.
     */
    public function testSymbolsWithMagnification()
    {
        $package = new \Metagist\ServerBundle\Entity\Package('test/test');
        $package->setOverallRating(5);
        $this->assertContains('fa-2x', $this->extension->symbols($package, 2));
    }
    
    /**
     * Ensures metainfos are traversed and checked agains specs.
     */
    public function testSymbolsWithMetainfos()
    {
        $package = new \Metagist\ServerBundle\Entity\Package('test/test');
        $package->setMetaInfos(array(\Metagist\ServerBundle\Entity\Metainfo::fromValue('maintainers', true)));
        
        $this->assertContains('fa-meh-o', $this->extension->symbols($package));
    }
    
    /**
     * Test the name of the extension.
     */
    public function testGetName()
    {
        $this->assertEquals('metagist_symbols', $this->extension->getName());
    }
}
