<?php


namespace App\Service\Order;


use App\Repository\OrderRepository;
use Symfony\Component\Form\FormInterface;

class OrderQueryBuilder
{

    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function create(FormInterface $form)
    {
        $paymentTypeFilter = $form->get('paymentType')->getData();
        $shopFilter = $form->get('shop')->getData();
        $fromDateFilter = $form->get('from')->getData();
        $toDateFilter = $form->get('to')->getData();
        $statusFilter = $form->get('status')->getData();

        $this->orderRepository->filter('paymentMethod', (string)$paymentTypeFilter)
            ->filter('shop', (int)$shopFilter)
            ->filter('createdAt', $fromDateFilter, '>=', 'createdAtFrom')
            ->filter('createdAt', $toDateFilter, '<=', 'createdAtTo')
            ->filterIn('status', $statusFilter);
    }

    public function forUser(int $userId)
    {
        $this->orderRepository->forUser($userId);
    }

    public function getQueryBuilder()
    {
        return $this->orderRepository->getQueryBuilder();
    }
}