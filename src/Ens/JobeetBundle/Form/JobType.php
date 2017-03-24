<?php

namespace Ens\JobeetBundle\Form;

use Ens\JobeetBundle\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class JobType extends AbstractType
{

    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('category', TextType::class, array('label' => 'Catégorie'));
        $builder->add('type', TextType::class, array('label' => 'Type'));
        $builder->add('company', TextType::class, array('label' => 'Compagnie'));
        $builder->add('logo', TextType::class, array('label' => 'Logo'));
        $builder->add('url', TextType::class, array('label' => 'Url'));
        $builder->add('position', TextType::class, array('label' => 'Position'));
        $builder->add('location', TextType::class, array('label' => 'Location'));
        $builder->add('description', TextType::class, array('label' => 'Description'));
        $builder->add('how_to_apply', TextType::class, array('label' => 'Comment postuler'));
        $builder->add('token');
        $builder->add('is_public', TextType::class, array('label' => 'est publique ?'));
        $builder->add('email', TextType::class, array('label' => 'e-mail'));
        $builder->add('type', ChoiceType::class, array('choices' => Job::getTypes(), 'expanded' => true));
    }

    public function getName()
    {
        return 'ens_jobeetbundle_jobtype';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ens\JobeetBundle\Entity\Job'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ens_jobeetbundle_job';
    }

}
