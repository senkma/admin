<?php

namespace App\Form;

use App\Entity\Supplier;
use App\Form\BankAccountType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Název'])
            ->add('street', TextType::class, ['label' => 'Ulice', 'required' => false])
            ->add('number', TextType::class, ['label' => 'Číslo popisné', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Město', 'required' => false])
            ->add('postal_code', TextType::class, ['label' => 'PSČ', 'required' => false])
            ->add('telephone', TextType::class, ['label' => 'Telefon', 'required' => false])
            ->add('invoice_email', TextType::class, ['label' => 'Fakturační email', 'required' => false])
            ->add('dic', TextType::class, ['label' => 'DIČ', 'required' => false])
            ->add('ico', TextType::class, ['label' => 'IČO', 'required' => false])
            ->add('description', TextType::class, ['label' => 'Popis', 'required' => false])
            ->add('bankAccounts', CollectionType::class, [
                'entry_type' => BankAccountType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Bankovní účty',
                'attr' => ['class' => 'bank-accounts-collection']
            ])
            ->add('vat_payer', CheckboxType::class, ['label' => 'Plátce DPH', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Supplier::class,
        ]);
    }
}