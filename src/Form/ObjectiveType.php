<?php

namespace App\Form;

use App\Entity\Objective;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ObjectiveType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter objective title'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control', 
                    'rows' => 5,
                    'placeholder' => 'Enter detailed description'
                ],
            ])
            ->add('pointsRequired', IntegerType::class, [
                'label' => 'Points Required',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Enter required points'
                ],
            ]);
            
        // Only admin users can assign objectives to other users
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getEmail();
                },
                'placeholder' => 'Select a user (optional)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objective::class,
        ]);
    }
} 