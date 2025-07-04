<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Supplier;
use App\Entity\Client;
use App\Entity\BankAccount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Název služby',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Popis služby',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
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
                'attr' => ['class' => 'form-control', 'id' => 'service_supplier'],
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
                'attr' => ['class' => 'form-control'],
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
                'attr' => ['class' => 'form-control', 'id' => 'service_bankAccount'],
                'query_builder' => function ($repo) use ($options) {
                    $qb = $repo->createQueryBuilder('ba');

                    if (isset($options['data']) && $options['data'] && $options['data']->getSupplier()) {
                        $qb->where('ba.supplier = :supplier')
                           ->setParameter('supplier', $options['data']->getSupplier());
                    } else {
                        $qb->where('1 = 0');
                    }

                    return $qb;
                },
            ])
            ->add('invoiceDay', IntegerType::class, [
                'label' => 'Den v měsíci pro vytvoření faktury',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 31,
                    'placeholder' => 'Zadejte den 1-31'
                ],
                'help' => 'Den v měsíci kdy se má vytvořit faktura (1-31)',
            ])
            ->add('dueDays', IntegerType::class, [
                'label' => 'Počet dní do splatnosti',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'placeholder' => 'Např. 14',
                    'id' => 'service_dueDays'
                ],
                'help' => 'Počet dní od vytvoření faktury do splatnosti (automaticky se načte z klienta)',
            ])
            ->add('frequency', ChoiceType::class, [
                'label' => 'Frekvence fakturace',
                'choices' => [
                    'Měsíčně' => 'monthly',
                    'Čtvrtletně' => 'quarterly',
                    'Ročně' => 'yearly',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Datum začátku služby',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Od kdy se má služba fakturovat (volitelné)',
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Datum konce služby',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Do kdy se má služba fakturovat (volitelné - prázdné = nekonečně)',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Aktivní služba',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Pouze aktivní služby se automaticky fakturují',
            ])
            ->add('items', CollectionType::class, [
                'entry_type' => ServiceItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Položky služby',
            ])
            ->add('sendEmail', CheckboxType::class, [
                'label' => 'Odeslat email při vykonání služby',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Pokud je zaškrtnuto, při vykonání služby se automaticky vytvoří komunikace a odešle email s fakturou',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
            'user' => null,
        ]);
    }
}
