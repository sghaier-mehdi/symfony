<?php
// src/Form/ConsultationCreateType.php (Form for Psychiatrist/Admin Creation)

namespace App\Form;

use App\Entity\Consultation;
use App\Entity\User; // Import User entity
use App\Repository\UserRepository; // Import User Repository
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // For selecting User entities (Patient/Psychiatrist)
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType; // For selecting date/time
use Symfony\Component\Form\Extension\Core\Type\IntegerType; // For duration
use Symfony\Component\Form\Extension\Core\Type\TextType; // For purpose string
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security; // Import Security service for role check
use Symfony\Component\Validator\Constraints\GreaterThan; // Constraint to ensure date is in the future
use Symfony\Component\Validator\Constraints\NotNull; // Often better than NotBlank for objects/dates
use Symfony\Component\Validator\Constraints\Length; // For purpose length

class ConsultationCreateType extends AbstractType
{
    private Security $security;
    // No need to inject UserRepository here just for the query_builder

    public function __construct(Security $security) // Inject the Security service
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         /** @var ?\App\Entity\User $currentUser */
         $currentUser = $this->security->getUser(); // Get the logged-in user

        $builder
            // Select Patient (Only users with ROLE_PATIENT)
            ->add('patient', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $er) { // Use UserRepository directly
                    return $er->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'PATIENT')
                        ->orderBy('u.fullName', 'ASC');
                },
                'choice_label' => 'fullName', // Display patient's full name
                'placeholder' => 'Select a Patient', // Optional empty value
                'constraints' => [ new NotNull(['message' => 'Please select a patient']) ],
                'label' => 'Patient',
            ]);

        // Only show the psychiatrist selection field to Admins.
        // For Psychiatrists creating a consultation, they will be set as the psychiatrist automatically in the controller.
        if ($this->security->isGranted('ROLE_ADMIN')) {
             $builder->add('psychiatrist', EntityType::class, [
                 'class' => User::class,
                 'query_builder' => function (UserRepository $er) { // Use UserRepository directly
                     return $er->createQueryBuilder('u')
                         ->where('u.role = :role')
                         ->setParameter('role', 'PSYCHIATRIST')
                         ->orderBy('u.fullName', 'ASC');
                 },
                 'choice_label' => 'fullName', // Display psychiatrist's full name
                 'placeholder' => 'Select a Psychiatrist',
                 'constraints' => [ new NotNull(['message' => 'Please select a psychiatrist']) ],
                 'label' => 'Psychiatrist',
             ]);
         }


         $builder
            // Consultation Date and Time
            ->add('consultationTime', DateTimeType::class, [
                 'widget' => 'single_text', // Use a single input field (HTML5 date/time picker)
                 'html5' => true, // Enable HTML5 picker if supported
                 'label' => 'Date and Time',
                 'constraints' => [
                    new NotNull(['message' => 'Please enter a consultation time']),
                    new GreaterThan(['value' => 'now', 'message' => 'Consultation time must be in the future']), // Ensure booking is for a future time
                 ],
            ])

            // Duration in Minutes (Optional, nullable in DB)
            ->add('durationMinutes', IntegerType::class, [
                'label' => 'Duration (minutes)',
                 'required' => false, // Matches DB nullable
                 'attr' => ['min' => 15, 'max' => 120], // Example: set min/max attributes
            ])

             // Purpose of Consultation (Optional, nullable in DB)
             ->add('purpose', TextType::class, [ // Use TextType as per DB, or TextareaType if expecting long text
                 'label' => 'Purpose',
                 'required' => false, // Matches DB nullable
                 'constraints' => [
                    new Length(['max' => 255]), // Matches DB column size
                 ],
            ])

            // status, notes, timestamps are handled in the controller/database
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
            // Add validation groups if needed later
        ]);
    }
}