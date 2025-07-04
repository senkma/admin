<?php

namespace App\Form;

use App\Entity\Communication;
use App\Entity\Supplier;
use App\Entity\Client;
use App\Entity\Service;
use App\Entity\Invoice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email příjemce',
                'attr' => ['class' => 'form-control'],
                'help' => 'Email adresa, na kterou bude zpráva odeslána',
                'required' => true
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Zpráva',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Napište zde zprávu...'
                ],
                'help' => 'Text zprávy, která bude odeslána',
                'required' => true
            ])
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
                'label' => 'Dodavatel',
                'required' => false,
                'placeholder' => '-- Vyberte dodavatele --',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function ($repo) use ($options) {
                    return $repo->createQueryBuilder('s')
                        ->where('s.user = :user')
                        ->setParameter('user', $options['user'])
                        ->orderBy('s.name', 'ASC');
                },
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'name',
                'label' => 'Klient',
                'required' => false,
                'placeholder' => '-- Vyberte klienta --',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function ($repo) use ($options) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $options['user'])
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'label' => 'Služba',
                'required' => false,
                'placeholder' => '-- Vyberte službu --',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function ($repo) use ($options) {
                    return $repo->createQueryBuilder('s')
                        ->where('s.user = :user')
                        ->setParameter('user', $options['user'])
                        ->orderBy('s.name', 'ASC');
                },
            ])
            ->add('invoice', EntityType::class, [
                'class' => Invoice::class,
                'choice_label' => 'invoice_number',
                'label' => 'Faktura',
                'required' => false,
                'placeholder' => '-- Vyberte fakturu --',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function ($repo) use ($options) {
                    return $repo->createQueryBuilder('i')
                        ->join('i.supplier', 's')
                        ->where('s.user = :user')
                        ->setParameter('user', $options['user'])
                        ->orderBy('i.date_created', 'DESC');
                },
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Připraveno' => 'pripraveno',
                    'Vykonáno' => 'vykonano',
                    'Zrušeno' => 'zruseno',
                ],
                'attr' => ['class' => 'form-control'],
                'help' => 'Status komunikace'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Communication::class,
            'user' => null,
        ]);
    }
}
