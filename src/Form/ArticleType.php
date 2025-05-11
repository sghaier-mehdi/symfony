<?php
// src/Form/ArticleType.php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category; // Import Category entity
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Import EntityType
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType; // Good for imageUrl if it's a URL
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url; // Optional for imageUrl

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a title.']),
                    new Length(['max' => 255]),
                ],
                'label' => 'Title',
            ])
            ->add('content', TextareaType::class, [
                'attr' => ['rows' => 10], // Make textarea larger
                'label' => 'Content',
                'required' => false, // Content is nullable in DB
            ])
            // Author is set automatically in the controller
            // ->add('author')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name', // Display category name in the dropdown
                'placeholder' => 'Choose a category', // Optional placeholder
                'required' => false, // category_id is nullable in your DB schema
                'label' => 'Category',
            ])
            ->add('isPublished', CheckboxType::class, [
                'label'    => 'Publish this article?',
                'required' => false, // Checkboxes are usually not strictly required
            ])
            ->add('imageUrl', UrlType::class, [ // Or TextType if it's just a path
                 'required' => false, // imageUrl is nullable in your DB schema
                 'label' => 'Image URL',
                 'constraints' => [
                     // Optional: Add URL validation if it must be a valid URL
                     // new Url(['message' => 'Please enter a valid URL.']),
                     new Length(['max' => 512]),
                 ]
            ])
            // createdAt and updatedAt are handled by the database or lifecycle callbacks
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}