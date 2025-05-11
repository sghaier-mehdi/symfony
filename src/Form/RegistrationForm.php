<?php
// src/Form/RegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Use TextType for username, fullName, phoneNumber
use Symfony\Component\Form\Extension\Core\Type\EmailType; // Use EmailType for email
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email; // Import Email constraint

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                 'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your username should be at least {{ limit }} characters',
                        'max' => 50, // Matches your DB column size
                    ]),
                ],
            ])
             ->add('fullName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your full name',
                    ]),
                    new Length([
                        'max' => 100, // Matches your DB column size
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                 'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email address',
                    ]),
                     new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                    new Length([
                        'max' => 100, // Matches your DB column size
                    ]),
                ],
            ])
            ->add('phoneNumber', TextType::class, [ // Use TextType for flexibility
                'required' => false, // Phone number is nullable in your DB
                'label' => 'Phone Number (Optional)',
                'constraints' => [
                     new Length([
                        'max' => 20, // Matches your DB column size
                    ]),
                    // Add Regex constraint for specific phone format if needed
                ],
            ])
            // Note: role is set in the controller, not on the form
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false, // This field is not mapped directly to the Entity property
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6, // Recommended minimum length
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096, // Max length allowed by Symfony for encoded passwords
                    ]),
                ],
            ])
            /*
            ->add('agreeTerms', CheckboxType::class, [ // Example if you need "agree to terms"
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default'], // Ensure validation runs
        ]);
    }
}