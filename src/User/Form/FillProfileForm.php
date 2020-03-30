<?php

namespace App\User\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FillProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => 'Fill your name'],
            ])
            ->add('bio', TextareaType::class, [
                'attr' => ['placeholder' => 'Fill your biography'],
                'required' => false,
            ])
            ->add('location', TextType::class, [
                'attr' => ['placeholder' => 'Fill your location'],
                'required' => false,
            ])
            ->add('website', TextType::class, [
                'attr' => ['placeholder' => 'Fill your website'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', FillProfileFormModel::class);
    }
}
