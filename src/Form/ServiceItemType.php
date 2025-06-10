<?php

namespace App\Form;

use App\Entity\ServiceItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Popis položky',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Množství',
                'attr' => [
                    'class' => 'form-control quantity-input',
                    'step' => '0.01',
                    'min' => '0.01'
                ],
            ])
            ->add('unit', TextType::class, [
                'label' => 'Jednotka',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ks, hod, měs...'
                ],
            ])
            ->add('unitPrice', NumberType::class, [
                'label' => 'Cena za jednotku',
                'attr' => [
                    'class' => 'form-control unit-price-input',
                    'step' => '0.01',
                    'min' => '0'
                ],
            ])
            ->add('vatRate', NumberType::class, [
                'label' => 'DPH (%)',
                'required' => false,
                'empty_data' => '21.00',
                'attr' => [
                    'class' => 'form-control vat-rate-input',
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '100'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceItem::class,
        ]);
    }
}
