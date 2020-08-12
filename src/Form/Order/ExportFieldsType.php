<?php


namespace App\Form\Order;


use App\Constant\OrderExportFields;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportFieldsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('id', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('id'),
            ])
            ->add('orderId', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('orderId'),
            ])
            ->add('shop_name', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('shop_name')
            ])
            ->add('erpExternalId', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('erpExternalId')
            ])
            ->add('utmCampaign', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('utmCampaign')
            ])
            ->add('link', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('link')
            ])
            ->add('createdAt', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('createdAt')
            ])
            ->add('paymentMethod', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('paymentMethod')
            ])
            ->add('shop_currency', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('shop_currency')
            ])
            ->add('itemsPrice', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('itemsPrice')
            ])
            ->add('shippingPrice', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('shippingPrice')
            ])
            ->add('total', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('total')
            ])
            ->add('note', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('note')
            ])

            ->add('name', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('name')
            ])
            ->add('phone', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('phone')
            ])
            ->add('email', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('email')
            ])
            ->add('region', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('region')
            ])
            ->add('city', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('city')
            ])
            ->add('zip', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('zip')
            ])
            ->add('address', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('address')
            ])
            ->add('house', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('house')
            ])
            ->add('entrance', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('entrance')
            ])
            ->add('floor', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('floor')
            ])
            ->add('flat', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('flat')
            ])
            ->add('orderItems', CheckboxType::class, [
                'label' => OrderExportFields::getLabel('orderItems')
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


}