<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Metagist\ServerBundle\Entity\PackageRepository;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Resources\CategorySchema;
use Metagist\ServerBundle\Resources\Validator;

/**
 * Tests the package repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageRepositoryTest extends WebTestCase
{
    /**
     * system under test
     * @var PackageRepository
     */
    private $repo;
    
    /**
     * Validator 
     * @var Validator
     */
    private $validator;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->validator = new Validator(
            new CategorySchema(file_get_contents(__DIR__ . '/testdata/testcategories.json'))
        );
        $kernel = self::createKernel();
        $kernel->boot();
        
        $this->repo = $kernel->getContainer()->get('doctrine')->getManager()->getRepository('MetagistServerBundle:Package');
        $this->repo->setValidator($this->validator);
    }
    
    /**
     * Ensures the params are validated.
     */
    public function testByAuthorAndNameExceptionIfWrongAuthor()
    {
        $this->setExpectedException("\InvalidArgumentException");
        $this->repo->byAuthorAndName(';;', ';;');
    }
    
    /**
     * Ensures the params are validated.
     */
    public function testByAuthorAndNameExceptionIfWrongName()
    {
        $this->setExpectedException("\InvalidArgumentException");
        $this->repo->byAuthorAndName('test', ';;');
    }
    
    /**
     * Ensures null is returned if the package has not been found.
     */
    public function testPackageNotFoundReturnsNull()
    {
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(false));
        
        $this->repo->byAuthorAndName('test', 'test');
    }
    
    /**
     * Ensures a package is returned if found.
     */
    public function testPackageIsFound()
    {
        $data = array(
            'id' => 1,
            'identifier' => 'test/test',
            'description' => 'test',
            'versions' => 'dev-master',
            'type' => 'library',
            'time_updated' => date('Y-m-d H:i:s')
        );
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($data));
        
        $this->repo->byAuthorAndName('test', 'test');
    }
    
    /**
     * Ensures a package collection is returned when LIKE - searching
     */
    public function testByIdentifierPart()
    {
        $data = array(
            'id' => 1,
            'identifier' => 'test/test',
            'description' => 'test',
            'versions' => 'dev-master',
            'type' => 'library',
            'time_updated' => date('Y-m-d H:i:s')
        );
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('fetch')
            ->will($this->returnValue($data));
        
        $result = $this->repo->byIdentifierPart('tes');
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $result);
    }
    
    /**
     * Ensures a package with an id is updated.
     */
    public function testSaveWithId()
    {
        $package = new Package('test/test', 123);
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));
        
        $this->repo->save($package);
    }
    
    /**
     * Ensures a package without an id is inserted.
     */
    public function testSaveWithoutId()
    {
        $package = new Package('test/test');
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));
        
        $this->repo->save($package);
        $this->assertEquals(123, $package->getId());
    }
    
    /**
     * Creates a statement mock, the provided HydratorMockStatement seems to be broken.
     * 
     * @param array $methods
     * @return Statement mock
     */
    protected function createMockStatement(array $methods = array('rowCount', 'fetch', 'lastInsertId'))
    {
        return $this->getMock('stdClass', $methods);
    }
}