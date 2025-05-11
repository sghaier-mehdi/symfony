<?php

namespace App\Form;

use App\Entity\Coupon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Coupon Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter coupon name'],
            ])
            ->add('discountPercentage', IntegerType::class, [
                'label' => 'Discount Percentage (%)',
                'attr' => [
                    'class' => 'form-control', 
                    'min' => 0, 
                    'max' => 100,
                    'placeholder' => 'Enter percentage (0-100)'
                ],
            ])
            ->add('code', TextType::class, [
                'label' => 'Coupon Code',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter coupon code'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
            ])
            ->add('expirationDate', DateTimeType::class, [
                'label' => 'Expiration Date',
                'widget' => 'single_text',
                'input' => 'datetime',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coupon::class,
        ]);
    }
} 