<?php
// src/Form/ReclamationType.php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // For typeReclamation and sentiment if manual
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la réclamation',
                'attr' => ['placeholder' => 'Ex: Problème de connexion']
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => ['rows' => 5, 'placeholder' => 'Décrivez votre problème ou suggestion...']
            ])
            ->add('typeReclamation', ChoiceType::class, [ // Assuming typeReclamation is a choice
                'label' => 'Type de réclamation',
                'choices' => [
                    'Technique' => 'TECHNIQUE',
                    'Facturation' => 'FACTURATION',
                    'Suggestion' => 'SUGGESTION',
                    'Autre' => 'AUTRE',
                ],
                'placeholder' => 'Choisissez un type',
            ]);

        // If you keep manual sentiment selection by the user:
        /*
        ->add('sentiment', ChoiceType::class, [
            'label' => 'Votre sentiment actuel concernant ce problème',
            'choices' => [
                'Frustré' => 'Frustré',
                'Neutre' => 'Neutre',
                'Satisfait' => 'Satisfait',
            ],
            'expanded' => true, // Renders as radio buttons
            'multiple' => false,
            'required' => false, // Or true if you want to force it
        ])
        */

        $builder
            ->add('urgent', CheckboxType::class, [
                'label' => 'Marquer comme urgent',
                'required' => false,
            ])
            ->add('followUp', CheckboxType::class, [
                'label' => 'Je souhaite un suivi pour cette réclamation',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}