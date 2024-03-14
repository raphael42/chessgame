<?php

namespace App\Form;

use App\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CreateGameWithFriend extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('color', ChoiceType::class, [
                'label' => 'Choisissez votre couleur',
                'choices' => array(
                    'Blanc' => 'white',
                    'Noir' => 'black',
                    'Aléatoire' => 'random',
                ),
                'expanded' => true,
            ])
            ->add('timePerPlayer', IntegerType::class, [
                'label' => 'Temps par joueur (en minutes)',
            ])
            ->add('secondsIncrement', IntegerType::class, [
                'label' => 'Incrémentation par coup (en secondes)',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Commencer',
            ])
        ;
    }
}
