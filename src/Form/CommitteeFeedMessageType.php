<?php

namespace AppBundle\Form;

use AppBundle\Committee\Feed\CommitteeMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFeedMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form--full b__nudge--bottom-medium',
                    'placeholder' => 'Entrez l\'objet de votre message',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Écrivez ici votre message public'],
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('published', CheckboxType::class, [
                'label' => 'Rendre ce message visible sur la page du comité',
                'attr' => ['class' => 'form__checkbox form__checkbox--large'],
                'required' => false,
            ])
            ->add('send', SubmitType::class, ['label' => 'Envoyer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CommitteeMessage::class);
    }
}
