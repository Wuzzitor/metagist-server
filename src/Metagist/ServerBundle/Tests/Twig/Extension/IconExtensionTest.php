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
        $icon = '<i class="symbol fa fa-star"></i>';
        $this->assertEquals(str_repeat($icon, 5), $result);
    }
    
    /**
     * Test the name of the extension.
     */
    public function testGetName()
    {
        $this->assertEquals('metagist_icons', $this->extension->getName());
    }
}
