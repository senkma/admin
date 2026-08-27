<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Client;
use App\Entity\Supplier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\BankAccount;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('invoice_number', TextType::class, ['label' => 'Číslo faktury'])
            ->add('date_created', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Datum vytvoření',
            ])
            ->add('date_due', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Datum splatnosti',
            ])
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
                'label' => 'Dodavatel',
                'query_builder' => function ($repo) use ($options) {
                    return $repo->createQueryBuilder('s')
                        ->where('s.user = :user')
                        ->setParameter('user', $options['user']);
                },
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'name',
                'label' => 'Klient',
                'query_builder' => function ($repo) use ($options) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $options['user']);
                },
            ])
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'choice_label' => function (BankAccount $bankAccount) {
                    return $bankAccount->getFullAccountNumber() .
                           ($bankAccount->getBankName() ? ' (' . $bankAccount->getBankName() . ')' : '') .
                           ($bankAccount->isDefault() ? ' - Výchozí' : '');
                },
                'label' => 'Bankovní účet',
                'required' => false,
                'placeholder' => '-- Vyberte bankovní účet --',
                'attr' => ['class' => 'form-control', 'id' => 'invoice_bankAccount'],
                // Load all accounts for the user's suppliers so create+JS selection
                // passes Symfony choice validation (dropdown is still filtered by JS).
                'query_builder' => function ($repo) use ($options) {
                    return $repo->createQueryBuilder('ba')
                        ->join('ba.supplier', 's')
                        ->where('s.user = :user')
                        ->setParameter('user', $options['user'])
                        ->orderBy('ba.id', 'ASC');
                },
            ])
            ->add('items', CollectionType::class, [
                'entry_type' => InvoiceItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Položky faktury',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
            'user' => null,
        ]);
    }
}