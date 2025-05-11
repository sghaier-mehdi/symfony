<?php
// src/Form/UserType.php (Admin User Management Form)

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface; // Import FormInterface for the closure
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Resolve the label string based on the form's options
        $passwordLabel = $options['require_password'] ? 'Password' : 'New Password (optional)';

        $builder
            ->add('username', TextType::class, [
                 'constraints' => [ new NotBlank(['message' => 'Please enter a username']) ],
                 'label' => 'Username',
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => $passwordLabel,
                'required' => false,

                'constraints' => [
                    new NotBlank([
                         'message' => 'Please enter a password',
                         'groups' => ['creation'],
                    ]),
                    new Length([
                         'min' => 6,
                         'minMessage' => 'Your password should be at least {{ limit }} characters',
                         'max' => 4096,
                         'groups' => ['Default', 'creation'],
                    ]),
                ],
            ])
            ->add('fullName', TextType::class, [
                'constraints' => [ new NotBlank(['message' => 'Please enter the full name']) ],
                'label' => 'Full Name',
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                     new NotBlank(['message' => 'Please enter an email address']),
                     new Email(['message' => 'Please enter a valid email address.'])
                ],
                'label' => 'Email Address',
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'label' => 'Phone Number',
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Patient' => 'PATIENT',
                    'Psychiatrist' => 'PSYCHIATRIST',
                    'Admin' => 'ADMIN',
                ],
                'label' => 'Role',
                'constraints' => [ new NotBlank(['message' => 'Please select a role']) ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => false, // Default value for the custom option

            // === CORRECTED SIGNATURE: Closure for validation_groups receives FormInterface ===
            'validation_groups' => function (FormInterface $form) {
                 $groups = ['Default'];
                 // Access the 'require_password' option from the form's resolved configuration
                 if ($form->getConfig()->getOption('require_password')) {
                      $groups[] = 'creation';
                 }
                 return $groups;
            },
            // =================================================================================
        ]);

        // Define the custom option and its allowed type
        $resolver->setRequired('require_password');
        $resolver->setAllowedTypes('require_password', 'bool');
    }
}