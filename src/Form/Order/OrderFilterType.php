<?php

namespace App\Form\Order;

use App\Constant\OrderStatuses;
use App\Repository\CurrencyRepository;
use App\Constant\Roles;
use App\Repository\PaymentTypeRepository;
use App\Repository\ShopRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class OrderFilterType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var ShopRepository
     */
    private $shopRepository;

    /**
     * @var PaymentTypeRepository
     */
    private $paymentTypeRepository;

    /**
     * OrderFilterType constructor.
     * @param ShopRepository $shopRepository
     * @param PaymentTypeRepository $paymentTypeRepository
     */
    public function __construct(
        ShopRepository $shopRepository,
        PaymentTypeRepository $paymentTypeRepository,
        Security $security,
        CurrencyRepository $currencyRepository
    )
    {
        $this->paymentTypeRepository = $paymentTypeRepository;
        $this->shopRepository = $shopRepository;
        $this->security = $security;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $shops = $this->shopRepository->createQueryBuilder('s')
            ->select('s.id,s.name')
            ->orderBy('s.name')
            ->getQuery()
            ->getResult();

        $paymentTypes = $this->paymentTypeRepository->createQueryBuilder('p')
            ->select('p.code,p.name')
            ->orderBy('p.name')
            ->getQuery()
            ->getResult();

        $currencies = $this->currencyRepository->createQueryBuilder('c')
            ->select('c.code,c.name,c.rate')
            ->where('c.rate is not null')
            ->orderBy('c.name')
            ->getQuery()
            ->getResult();

        $mappedShops = $this->getShops();
        $mappedPTypes = $this->getPaymentTypes();

        $mappedCurrencies = [];
        foreach ($currencies as $currency) {
            $mappedCurrencies[$currency['name']] = $currency['code'];
        }

        $builder
            ->add('shop', ChoiceType::class, [
                'label' => 'Магазин',
                'choices' => $mappedShops,
                'required' => false
            ])
            ->add('paymentType', ChoiceType::class, [
                'label' => 'Тип оплаты',
                'choices' => $mappedPTypes,
                'required' => false
            ])
            ->add('from', DateType::class, [
                'label' => 'Оформлены с даты',
                'widget' => 'single_text',
                'input' => 'string',
                'input_format' => 'Y-m-d',
                'required' => false
            ])
            ->add('to', DateType::class, [
                'label' => 'Оформлены до даты',
                'widget' => 'single_text',
                'input' => 'string',
                'input_format' => 'Y-m-d',
                'required' => false
            ])
            ->add('currency', ChoiceType::class, [
                'label' => 'Валюта',
                'choices' => $mappedCurrencies,
                'required' => false,
                'placeholder' => '- Выберите валюту -',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Выберите статус',
                'choices' => array_flip(OrderStatuses::MAP),
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('csv_fields', HiddenType::class, [
                'label' => '',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Применить',
                'attr' => ['class' => 'btn btn-primary save'],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
                'method' => 'GET'
            ]
        );
    }

    private function getShops()
    {
        $shops = $this->shopRepository->createQueryBuilder('s')
            ->select('s.id,s.name')
            ->orderBy('s.name');

        if ($this->security->getUser()->getRoles()[0] === Roles::ORDER_MANAGER) {
            $shops->leftJoin('s.managers', 'm')
                ->where('m.id = :userId')
                ->setParameter(':userId', $this->security->getUser()->getId());
        }

        $shops = $shops->getQuery()
            ->getResult();

        $mappedShops = [];
        foreach ($shops as $shop) {
            $mappedShops[$shop['name']] = $shop['id'];
        }

        return $mappedShops;
    }

    private function getPaymentTypes()
    {
        $paymentTypes = $this->paymentTypeRepository->createQueryBuilder('p')
            ->select('p.code,p.name')
            ->orderBy('p.name')
            ->getQuery()
            ->getResult();

        $mappedPTypes = [];
        foreach ($paymentTypes as $type) {
            $mappedPTypes[$type['name']] = $type['code'];
        }

        return $mappedPTypes;
    }
}