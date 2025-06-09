<?php

namespace App\Form;

use App\Entity\BankAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $banks = [
            'Vyberte banku...' => '',
            'Komerční banka' => ['code' => '0100', 'swift' => 'KOMBCZPP'],
            'Česká spořitelna' => ['code' => '0800', 'swift' => 'GIBACZPX'],
            'ČSOB' => ['code' => '0300', 'swift' => 'CEKOCZPP'],
            'UniCredit Bank' => ['code' => '2700', 'swift' => 'BACXCZPP'],
            'Raiffeisenbank' => ['code' => '5500', 'swift' => 'RZBCCZPP'],
            'mBank' => ['code' => '6210', 'swift' => 'BREXCZPP'],
            'Fio banka' => ['code' => '2010', 'swift' => 'FIOBCZPP'],
            'Air Bank' => ['code' => '3030', 'swift' => 'AIRACZPP'],
            'Equa bank' => ['code' => '6100', 'swift' => 'EQBKCZPP'],
            'Jiná banka' => 'custom'
        ];

        $builder
            ->add('bank_selector', ChoiceType::class, [
                'label' => 'Vyberte banku',
                'choices' => $banks,
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'bank-selector']
            ])
            ->add('account_number', TextType::class, [
                'label' => 'Číslo účtu',
                'required' => true,
                'attr' => ['placeholder' => 'např. 123456789']
            ])
            ->add('bank_code', TextType::class, [
                'label' => 'Kód banky',
                'required' => false,
                'attr' => ['placeholder' => 'např. 0100', 'class' => 'bank-code-input', 'style' => 'width: 100px;']
            ])
            ->add('bank_name', TextType::class, [
                'label' => 'Název banky',
                'required' => false,
                'attr' => ['placeholder' => 'např. Komerční banka', 'class' => 'bank-name-input']
            ])
            ->add('iban', TextType::class, [
                'label' => 'IBAN',
                'required' => false,
                'attr' => ['placeholder' => 'např. CZ6508000000192000145399']
            ])
            ->add('swift', TextType::class, [
                'label' => 'SWIFT/BIC',
                'required' => false,
                'attr' => ['placeholder' => 'např. KOMBCZPP', 'class' => 'swift-input']
            ])
            ->add('is_default', CheckboxType::class, [
                'label' => 'Výchozí účet',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAccount::class,
        ]);
    }
}
