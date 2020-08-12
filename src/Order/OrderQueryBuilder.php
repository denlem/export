<?php


namespace App\Service\Order;


use App\Constant\Roles;
use App\Repository\OrderRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderQueryBuilder
{
    private UserInterface $user;

    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository, Security $security)
    {
        $this->user = $security->getUser();
        $this->orderRepository = $orderRepository;
    }

    public function create(FormInterface $form)
    {
        $qb = $this->orderRepository->createQueryBuilder('o');

        $paymentTypeFilter = $form->get('paymentType')->getData();
        $shopFilter = $form->get('shop')->getData();
        $fromDateFilter = $form->get('from')->getData();
        $toDateFilter = $form->get('to')->getData();
        $statusFilter = $form->get('status')->getData();
        $utmCampaignFilter = $form->get('utmCampaign')->getData();

        if ($this->user->getRoles()[0] === Roles::ORDER_MANAGER) {
            $qb->leftJoin('o.shop', 's')
                ->leftJoin('s.managers', 'm')
                ->andWhere('m.id = :userId')
                ->setParameter(':userId', $this->user->getId());
        }

        if ($shopFilter) {
            $qb->andWhere('o.shop = :shop')
                ->setParameter('shop', $shopFilter);
        }

        if ($statusFilter) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $statusFilter);
        }

        if ($utmCampaignFilter) {
            $qb->andWhere('o.utmCampaign = :utmCampaign')
                ->setParameter('utmCampaign', $utmCampaignFilter);
        }

        if ($paymentTypeFilter) {
            $qb->andWhere('o.paymentMethod = :paymentType')
                ->setParameter('paymentType', $paymentTypeFilter);
        }

        if ($fromDateFilter) {
            $qb->andWhere($qb->expr()->gte('o.createdAt', ':fromDate'))
                ->setParameter('fromDate', $fromDateFilter);
        }

        if ($toDateFilter) {
            $toDateFilter = (new \DateTime($toDateFilter));

            $qb->andWhere($qb->expr()->lte('o.createdAt', ':toDate'))
                ->setParameter('toDate', $toDateFilter->format('Y-m-d 23:59:59'));
        }

        if ($statusFilter) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $statusFilter);
        }

        return $qb;
    }
}