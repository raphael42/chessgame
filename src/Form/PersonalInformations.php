<?php

namespace App\Form;

use App\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonalInformations extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nickname', TextType::class, [
                'label' => 'Votre nom d\'utilisateur',
                'data' => $options['nickname'] ?? null,
            ])
            ->add('email', TextType::class, [
                'label' => 'Votre email',
                'data' => $options['email'] ?? null,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Définir les options par défaut
        ]);

        // Ajoute une option personnalisée
        $resolver->setDefined(['nickname']);
        $resolver->setDefined(['email']);
    }
}
