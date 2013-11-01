<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Entity\PackageRepository;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Validation\Validator;
use Metagist\ServerBundle\Validation\CategorySchema;

/**
 * Tests the package repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageRepositoryTest extends WebDoctrineTestCase
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
        $this->repo = $this->entityManager->getRepository('MetagistServerBundle:Package');
        
        $this->validator = new Validator(
            new CategorySchema(file_get_contents(__DIR__ . '/testdata/testcategories.json'))
        );
        $this->repo->setValidator($this->validator);
    }
    
    protected function loadFixtures()
    {
        $faker = \Faker\Factory::create();
        
        $this->package = new Package('test/test123');
        $this->package->setDescription($faker->text);
        $this->entityManager->persist($this->package);
        $this->entityManager->flush();
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
        $this->assertNull(
            $this->repo->byAuthorAndName('test', 'test')
        );
    }
    
    /**
     * Ensures a package is returned if found.
     */
    public function testPackageIsFound()
    {
        $package = $this->repo->byAuthorAndName('test', 'test123');
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Package", $package);
    }
    
    /**
     * Ensures a package collection is returned when LIKE - searching
     */
    public function testByIdentifierPart()
    {
        $result = $this->repo->byIdentifierPart('tes');
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Package", $result->first());
    }
    
    /**
     * Ensures a package is saved
     */
    public function testSave()
    {
        $package = new Package('test/test');
        $package->setDescription("desc");
        $this->repo->save($package);
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