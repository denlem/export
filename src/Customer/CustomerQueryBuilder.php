<?php


namespace App\Service\Customer;


use App\Repository\CustomerRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

class CustomerQueryBuilder
{

    private CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param FormInterface $form
     * @return QueryBuilder
     */
    public function create(FormInterface $form): QueryBuilder
    {
        $name = $form->get('name')->getData();
        $phone = $form->get('phone')->getData();
        $email = $form->get('email')->getData();
        $city = $form->get('city')->getData();
        $create_date = $form->get('create_date')->getData();
        $shop_id = $form->get('shop')->getData();

//        $qb = $this->customerRepository->filterLike('name', (string) $name )
//            ->filterLike('phone', (int) $phone, )
//            ->filterLike('email', (string) $email, )
//            ->filterLike('city', (string) $city, )
//            ->getQueryBuilder()
//            ->select(array('o', 'c', 'i', 's', 'ord'))
////            ->andWhere('(o.name is not null AND o.phone is not null AND o.email is not null)')
//            ->leftJoin('o.carts', 'c')
//            ->leftJoin('c.shop', 's')
//            ->leftJoin('c.orders', 'ord')
//            ->andWhere("(ord.status = 'erp_created' OR ord.status = 'created')")
//            ->join('c.cartItems', 'i');

        $qb = $this->customerRepository->filterLike('name', (string) $name )
            ->filterLike('phone', (int) $phone, )
            ->filterLike('email', (string) $email, )
            ->filterLike('city', (string) $city, )
            ->getQueryBuilder()
            ->select(array('o', 'c', 'cc', 'ord', 's'))
            ->leftJoin('o.carts', 'c')
            ->leftJoin('o.carts', 'cc')
            ->leftJoin('c.orders', 'ord')
            ->leftJoin('cc.shop', 's')
            ->andWhere("(ord.status = 'erp_created' OR ord.status = 'created')")
        ;
        if ($create_date !== null && $create_date!="") {
            $create_date_till = $form->get('create_date')->getNormData()->modify( '+1 day' )->format('Y-m-d');
            $qb->andWhere("(o.createdAt >= '".$create_date."' AND o.createdAt < '".$create_date_till."')");
        }
        if ($shop_id !== null) {
            $qb->andWhere("s.id = :shop_id")->setParameter('shop_id', $shop_id);
        }

        return $qb;
    }
}