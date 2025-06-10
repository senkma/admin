<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Jméno']) // Vyžadováno
            ->add('street', TextType::class, ['label' => 'Ulice', 'required' => false])
            ->add('number', TextType::class, ['label' => 'Číslo popisné', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Město', 'required' => false])
            ->add('postal_code', TextType::class, ['label' => 'PSČ', 'required' => false])
            ->add('telephone', TextType::class, ['label' => 'Telefon', 'required' => false])
            ->add('invoice_email', TextType::class, ['label' => 'Fakturační email', 'required' => false])
            ->add('dic', TextType::class, ['label' => 'DIČ', 'required' => false])
            ->add('ico', TextType::class, ['label' => 'IČO', 'required' => false])
            ->add('variable_symbol', TextType::class, ['label' => 'Variabilní symbol', 'required' => false])
            ->add('description', TextType::class, ['label' => 'Popis', 'required' => false])
            ->add('dueDays', IntegerType::class, [
                'label' => 'Splatnost (dny)',
                'attr' => [
                    'min' => 1,
                    'max' => 365,
                    'placeholder' => '14'
                ],
                'help' => 'Počet dní splatnosti pro faktury tohoto klienta'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}