<?php
// src/Form/ConsultationManageType.php (Form for Psychiatrist/Admin Management)

namespace App\Form;

use App\Entity\Consultation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // For status dropdown
use Symfony\Component\Form\Extension\Core\Type\TextareaType; // For notes
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank; // Optional for status

class ConsultationManageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Status Dropdown - Must match your DB ENUM options
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Scheduled' => 'SCHEDULED', // Map user-friendly labels to DB values
                    'Completed' => 'COMPLETED',
                    'Cancelled' => 'CANCELLED',
                    'Rescheduled' => 'RESCHEDULED',
                    // Add other statuses from your DB ENUM as needed (ensure they match your DB)
                ],
                'label' => 'Status',
                'constraints' => [
                     new NotBlank(['message' => 'Please select a status']),
                ],
                 'placeholder' => 'Select status', // Optional empty value
            ])
            // Notes Textarea
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false, // Notes are nullable in DB
                'attr' => ['rows' => 5], // Optional: increase textarea height
            ])

            // Exclude patient, psychiatrist, time, duration, purpose, timestamps
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}