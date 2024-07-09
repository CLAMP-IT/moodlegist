<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MoodleVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class)
            ->add('moodle_version', ChoiceType::class, array(
                'choices' => array(
                    'Any version of Moodle' => 'any',
                    'Moodle 4.4' => '4.4',
                    'Moodle 4.3' => '4.3',
                    'Moodle 4.2' => '4.2',
                    'Moodle 4.1' => '4.1',
                    'Moodle 4.0' => '4.0',
                    'Moodle 3.11' => '3.11',
                    'Moodle 3.10' => '3.10',
                    'Moodle 3.9' => '3.9',
                    'Moodle 3.8' => '3.8',
                    'Moodle 3.7' => '3.7',
                    'Moodle 3.6' => '3.6',
                    'Moodle 3.5' => '3.5',
                    'Moodle 3.4' => '3.4',
                    'Moodle 3.3' => '3.3',
                    'Moodle 3.2' => '3.2',
                    'Moodle 3.1' => '3.1',
                    'Moodle 3.0' => '3.0',
                    'Moodle 2.9' => '2.9',
                    'Moodle 2.8' => '2.8',
                    'Moodle 2.7' => '2.7',
                ),
            ))
            ->add('search', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
