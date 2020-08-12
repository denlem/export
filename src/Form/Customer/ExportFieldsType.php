<?php


namespace App\Form\Customer;


use App\Constant\CustomerExportFields;
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
                'label' => CustomerExportFields::getLabel('id'),
            ])
            ->add('uuid', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('uuid'),
            ])
            ->add('name', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('name')
            ])
            ->add('phone', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('phone')
            ])
            ->add('email', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('email')
            ])
            ->add('region', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('region')
            ])
            ->add('city', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('city')
            ])
            ->add('zip', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('zip')
            ])
            ->add('address', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('address')
            ])
            ->add('house', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('house')
            ])
            ->add('entrance', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('entrance')
            ])
            ->add('floor', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('floor')
            ])
            ->add('flat', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('flat')
            ])
            ->add('shops', CheckboxType::class, [
                'label' => CustomerExportFields::getLabel('shops')
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