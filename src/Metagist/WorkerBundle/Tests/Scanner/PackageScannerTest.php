<?php
namespace Metagist\WorkerBundle\Tests\Scanner;

use Metagist\WorkerBundle\Scanner\PackageScanner;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Tests the package scanner decorator.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     *  
     * @var PackageScanner
     */
    private $scanner;
    
    /**
     * app 
     * @var Application
     */
    private $apiMock;
    
    /**
     * server client mock
     * 
     * @var \Metagist\Api\ServerInterface
     */
    private $serverMock;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->scanner = new PackageScanner($this->getMock("\Psr\Log\LoggerInterface"));
    }
    
    /**
     * Ensures that a configured scanner instance is called.
     */
    public function testScan()
    {
        $scannerMock = $this->getMock("\Metagist\WorkerBundle\Scanner\ScannerInterface");
        $package = new Package('test/test');
        $scannerMock->expects($this->once())
            ->method('scan')
            ->with($package);
        $this->scanner->addScanner($scannerMock);
        
        $this->scanner->scan($package);
    }
}