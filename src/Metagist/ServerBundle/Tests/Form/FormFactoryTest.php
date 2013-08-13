<?php
namespace Metagist\ServerBundle\Tests\Form;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Tests\Fixtures\FakeMetadataFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Metagist\ServerBundle\Resources\CategorySchema;

/**
 * Tests the metagist form factory.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class FormFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var FormFactory
     */
    private $factory;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $metadata = new ClassMetadata('Symfony\Component\Form\Form');
        $fakeFactory = new FakeMetadataFactory();
        $fakeFactory->addMetadata($metadata);
        
        $validator = $this->getMock("\Symfony\Component\Validator\ValidatorInterface");
        $validator->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($fakeFactory));
        
        $symFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension($validator))
            ->getFormFactory()
            ;
        $schema     = new CategorySchema(file_get_contents(__DIR__ . '/testdata/testcategories.json'));
        $this->factory = new FormFactory($symFactory, $schema);
    }
    
    /**
     * Ensures an instance of \Symfony\Component\Form\Form is created and returned.
     */
    public function testGetContributeForm()
    {
        $versions = array('test');
        $form = $this->factory->getContributeForm($versions, 'string');
        $this->assertInstanceOf("\Symfony\Component\Form\Form", $form);
    }
    
    /**
     * Ensures the factory checks the type.
     */
    public function testGetContributeFormThrowsException()
    {
        $this->setExpectedException("\InvalidArgumentException");
        $this->factory->getContributeForm(array('test'), 'unknown');
    }
    
    /**
     * Ensures an instance of \Symfony\Component\Form\Form is created and returned.
     */
    public function testGetRateForm()
    {
        $versions = array('test');
        $form = $this->factory->getRateForm($versions);
        $this->assertInstanceOf("\Symfony\Component\Form\Form", $form);
    }
}
