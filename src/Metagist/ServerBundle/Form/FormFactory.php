<?php
namespace Metagist\ServerBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Factory for forms.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class FormFactory
{
    /**
     * the categories
     * @var CategorySchema 
     */
    private $schema;
   
    /**
     * symfony form factory
     * @var Symfony\Component\Form\FormFactory 
     */
    private $formFactory;
    
    /**
     * maps form field type to metainfo type
     * @var array 
     */
    private $types;
    
    /**
     * Constructor.
     * 
     * @param \Symfony\Component\Form\FormFactory $factory
     * @param \Metagist\CategorySchema           $schema
     */
    public function __construct(\Symfony\Component\Form\FormFactory $factory, CategorySchema $schema)
    {
        $this->formFactory = $factory;
        $this->schema      = $schema;
        $this->types = array(
            'string'    => array('text',     new Assert\Type(array('type' => 'string'))),
            'url'       => array('text',     new Assert\Url()),
            'badge'     => array('text',     new Assert\Url()),
            'integer'   => array('number',   new Assert\Type(array('type' => 'numeric'))),
            'boolean'   => array('checkbox', new Assert\Type(array('type' => 'boolean'))),
        );
    }
    
    /**
     * Returns the rating form.
     * 
     * @param array $versions
     * @param \Metagist\Rating $rating
     * @return \Symfony\Component\Form\Form
     */
    public function getRateForm(array $versions = array(''), Rating $rating = null)
    {
        $data = array();
        if ($rating !== null) {
            $data = array(
                'version' => $rating->getVersion(),
                'rating'  => $rating->getRating(),
                'title'   => $rating->getTitle(),
                'comment' => $rating->getComment()
            );
        }
        
        $builder = $this->formFactory->createBuilder('form', $data);

        $choices = array(
            1 => '1 (lowest rating)',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5 (highest rating)',
        );
            
        $form = $builder
            ->add('rating', 'choice', array(
                'choices' => $choices,
                'constraints' => new Assert\Range(array('min' => 1, 'max' => 5)),
            ))
            ->add('version', 'choice', array(
                'choices' => array('') + $versions,
                'multiple' => false,
                'expanded' => false
            ))
            ->add('title', 'text', array(
                'constraints' => new Assert\NotNull(),
            ))
            ->add('comment', 'textarea')
            ->getForm()
        ;
        
        return $form;
    }
    
    /**
     * Returns the form for metainfo contribution.
     * 
     * @param array  $versions
     * @param string $type
     * @return \Symfony\Component\Form\Form
     */
    public function getContributeForm(array $versions = array(''), $type)
    {
        if (!isset($this->types[$type])) {
            throw new \InvalidArgumentException('unknown type ' . $type);
        }
        $fieldType  = $this->types[$type][0];
        $constraint = array(new Assert\NotBlank(), $this->types[$type][1]);
        
        $builder = $this->formFactory->createBuilder('form');
        $form = $builder
            ->add('version', 'choice', array(
                'choices' => array('') + $versions,
                'multiple' => false,
                'expanded' => false
            ))
            ->add('value', $fieldType, array(
                'constraints' => $constraint,
            ))
            ->getForm()
        ;
        
        return $form;
    }
}