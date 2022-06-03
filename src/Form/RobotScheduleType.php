<?php

namespace App\Form;

use App\Entity\CrawlSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RobotScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'attr' => [
                    'readonly' => $options['address_readonly'],
                ]
            ])
            ->add('agent', TextType::class)
            ->add('delay', NumberType::class, [
                'html5' => true,
            ])
            ->add('ignoreQuery', CheckboxType::class, [
                'label' => 'Ignore query?', 
                'required' => false,
                'data' => $options['ignore_query'],
            ])
            ->add('importSitemaps', CheckboxType::class, [
                'label' => 'Import sitemaps?',
                'required' => false,
                'data' => $options['import_sitemaps'],
            ])
            ->add('retryMax', NumberType::class, [
                'label' => 'Retry max',
                'html5' => true,
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Start time',
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['save_button_label'],
                'attr' => ['style' => 'float: left'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CrawlSettings::class,
            'save_button_label' => 'Create',
            'delete_button_hidden' => true,
            'ignore_query' => true,
            'import_sitemaps' => true,
            'address_readonly' => false,
        ]);
    }
}
