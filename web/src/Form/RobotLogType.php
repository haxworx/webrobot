<?php

namespace App\Form;

use App\Entity\CrawlSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RobotLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('crawl', EntityType::class, [
                'class' => CrawlSettings::class,
                'choice_label' => 'address',
                'choices' => $options['crawlers'],
                'label' => 'Address',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'crawlers' => [],
        ]);
    }
}
