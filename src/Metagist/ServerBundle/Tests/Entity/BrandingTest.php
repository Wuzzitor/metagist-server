<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\Branding;

/**
 * Tests the branding class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class BrandingTest extends \PHPUnit_Framework_TestCase
{
    
    public function testGetLessWithVendor()
    {
        $branding = new Branding();
        $branding->setVendor('test1');
        $branding->setLess('');
        $this->assertContains('.test1', $branding->getLessWithVendor());
    }
}