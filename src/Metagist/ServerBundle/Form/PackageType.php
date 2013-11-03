<?php

namespace Metagist\ServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PackageType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identifier')
            ->add('description')
            ->add('type')
            ->add('branding')
            ->add('categories', 'entity', array(
                'class' => 'MetagistServerBundle:Category',
                'multiple' => true,
                'expanded' => true,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Metagist\ServerBundle\Entity\Package'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'metagist_serverbundle_package';
    }
}
