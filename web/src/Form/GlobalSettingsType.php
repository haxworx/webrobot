<?php

namespace App\Form;

use App\Entity\GlobalSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class GlobalSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('maxCrawlers')
            ->add('debug')
            ->add('dockerImage')
            ->add('mqttHost')
            ->add('mqttPort')
            ->add('mqttTopic')
            ->add('save', SubmitType::class, ['label' => 'Save'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GlobalSettings::class,
        ]);
    }
}
