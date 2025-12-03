<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de l’adresse',
                'required' => false,
                'attr' => ['placeholder' => 'Domicile, bureau…'],
            ])
            ->add('addressLine1', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
            ])
            ->add('addressLine2', TextType::class, [
                'label' => 'Complément',
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
            ])
            ->add('state', TextType::class, [
                'label' => 'Région / Département',
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'required' => false,
                'placeholder' => 'Sélectionner un pays',
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'Adresse principale',
                'required' => false,
            ])
            ->add('isShipping', CheckboxType::class, [
                'label' => 'Utiliser pour la livraison',
                'required' => false,
            ])
            ->add('isBilling', CheckboxType::class, [
                'label' => 'Utiliser pour la facturation',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
