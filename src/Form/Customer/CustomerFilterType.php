<?php


namespace App\Form\Customer;


use App\Repository\ShopRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CustomerFilterType extends AbstractType
{
    /**
     * CustomerFilterType constructor.
     * @param ShopRepository $shopRepository
     */
    public function __construct(
        ShopRepository $shopRepository
    )
    {
        $this->shopRepository = $shopRepository;
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
            ->getArrayResult();
        $mappedShops = [];
        foreach ($shops as $shop) {
            $mappedShops[$shop['name']] = $shop['id'];
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Имя',
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Телефон',
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'Емейл',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'Город',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255, 'allowEmptyString' => true]),
                ],
            ])
            ->add('shop', ChoiceType::class, [
                'label' => 'Магазин',
                'choices' => $mappedShops,
                'required' => false
            ])
            ->add('create_date', DateType::class, [
                'label' => 'Дата создания',
                'widget' => 'single_text',
                'input' => 'string',
                'input_format' => 'Y-m-d',
                'required' => false
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
}