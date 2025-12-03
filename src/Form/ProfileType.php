<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Address $primaryAddress */
        $primaryAddress = $options['primary_address'];

        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('avatarFile', FileType::class, [
                'label' => 'Avatar',
                'mapped' => false,
                'required' => false,
                'help' => 'PNG ou JPG jusqu’à 2 Mo',
            ])
            ->add('avatarPath', HiddenType::class, [
                'required' => false,
            ])
            ->add('newsletterOptIn', CheckboxType::class, [
                'label' => 'Recevoir les nouveautés TechNova',
                'required' => false,
            ])
            ->add('primaryAddress', AddressType::class, [
                'mapped' => false,
                'data' => $primaryAddress,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'primary_address' => fn () => (new Address())
                ->setIsDefault(true)
                ->setIsBilling(true)
                ->setIsShipping(true),
        ]);
    }
}
