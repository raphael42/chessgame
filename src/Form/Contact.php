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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\Validator\Constraints\IdenticalTo;

class Contact extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre prénom',
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
            ])
            ->add('captcha', TextType::class, [
                'label' => 'Recopiez le texte : <img src="assets/img/captcha/'.$options['code_session'].'.png">',
                'label_html' => true,
                'constraints' => [
                    new IdenticalTo([
                        // 'value' => strtoupper($options['code_session']) || strtolower($options['code_session']),
                        'value' => $options['code_session'],
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Définir les options par défaut
        ]);

        // Ajoute une option personnalisée
        $resolver->setDefined(['code_session']);
    }
}
