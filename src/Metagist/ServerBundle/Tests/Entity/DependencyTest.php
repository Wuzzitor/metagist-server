<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\Dependency;

/**
 * Tests the dependency class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class DependencyTest extends \PHPUnit_Framework_TestCase
{
    
    public function testGetAuthor()
    {
        $dep = new Dependency();
        $dep->setDependencyIdentifier('test/abc');
        $this->assertEquals('test', $dep->getAuthor());
    }
    
    public function testGetName()
    {
        $dep = new Dependency();
        $dep->setDependencyIdentifier('test/abc');
        $this->assertEquals('abc', $dep->getName());
    }
}