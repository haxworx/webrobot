<?php

namespace App\Form;

use App\Entity\CrawlSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'placeholder' => 'Choose a log',
                'data' => $options['crawler'],
            ])
        ;
        if ($options['scan_dates']) {
            $builder->add('dates', ChoiceType::class, [
                'choices' => array_combine($options['scan_dates'], $options['scan_dates']),
                'data' => $options['scan_date'],
                'placeholder' => 'Choose a date',
            ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'crawlers' => [],
            'crawler' => null,
            'scan_dates' => null,
            'scan_date' => null,
        ]);
    }
}
