<?php

// src/Form/GlobalSettingsType.php

namespace App\Form;

use App\Entity\GlobalSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
            ->add('mqttHost', TextType::class, ['attr' => ['readonly' => true ]])
            ->add('mqttPort', NumberType::class, ['attr' => ['readonly' => true ]])
            ->add('mqttTopic', TextType::class, ['attr' => ['readonly' => true ]])
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
