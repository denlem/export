<?php

namespace App\Controller;

use App\Constant\AbTestStatus;
use App\Constant\OrderStatuses;
use App\Entity\Currency;
use App\Constant\Roles;
use App\Entity\Order;
use App\Form\Order\ExportFieldsType;
use App\Form\Order\OrderFilterType;
use App\Form\Order\OrderUpdateType;
use App\Repository\AbTestRepository;
use App\Repository\OrderRepository;
use App\Service\Erp\ErpClientInterface;
use App\Service\Erp\OrderErpManager\OrderDataCreatorInterface;
use App\Service\Erp\OrderErpManager\OrderErpManagerInterface;
use App\Service\Export\ExportInterface;
use App\Service\Order\OrderCurrencyManager;
use App\Service\Order\OrderQueryBuilder;
use App\Service\Order\OrderStateManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("orders", name="order_")
 */
class OrderController extends AbstractController
{
    /**
     * @IsGranted("view_order")
     * @Route(name="index", methods={"GET"})
     */
    public function index(
        PaginatorInterface $paginator,
        Request $request,
        OrderCurrencyManager $orderCurrencyManager,
        OrderQueryBuilder $orderQueryBuilder
    )
    {
        $form = $this->createForm(OrderFilterType::class)
            ->handleRequest($request);

        $exportForm = $this->createForm(ExportFieldsType::class)->handleRequest($request);

        $qb = $orderQueryBuilder->create($form);

        $page = $request->query->getInt('page', 1);

        $orders = $paginator->paginate($qb, $page, 20, [
            'defaultSortFieldName' => ['o.id'],
            'defaultSortDirection' => 'desc',
        ]);

        $currencyCode = $form->get('currency')->getViewData();
        $sumData = $orderCurrencyManager->getOrderSumm($currencyCode, $qb);

        return $this->render('order/index.html.twig', [
            'statuses' => OrderStatuses::MAP,
            'orders' => $orders,
            'form' => $form->createView(),
            'sumData' => $sumData,
            'exportForm' => $exportForm->createView()
        ]);
    }

    /**
     * @param Order $order
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/{id}/update", name="update", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function update(Order $order, EntityManagerInterface $em, Request $request)
    {
        $form = $this->createForm(OrderUpdateType::class, $order)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Данные заказа обновлены');
            return $this->redirectToRoute('order_view', [
                'id' => $order->getId(),
            ]);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("view_order")
     * @Route("/{id}", name="view", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function view(int $id, OrderRepository $orderRepository, AbTestRepository $abTestRepository)
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            $this->addFlash('error', 'Заказ не найден');
            return $this->redirectToRoute('order_index');
        }

        $abTests = $abTestRepository->getTestsByIds($order->getAbTestIds());
        return $this->render('order/view.html.twig', [
            'order' => $order,
            'abTests' => $abTests,
            'abTestStatuses' => AbTestStatus::MAP,
            'erpLogs' => $order->getErpLogs(),
            'statuses' => OrderStatuses::MAP
        ]);
    }

    /**
     * @IsGranted("view_order")
     * @Route("/export/csv", name="export_csv", methods={"GET", "POST"})
     */
    public function exportcsv(
        Request $request,
        OrderQueryBuilder $orderQueryBuilder,
        ExportInterface $orderExport
    )
    {
        $form = $this->createForm(OrderFilterType::class)
            ->handleRequest($request);

        $orderQueryBuilder->create($form);
        $qb = $orderQueryBuilder->getQueryBuilder();
        $fieldsCsv = $form->get('csv_fields')->getData();
        $currencyCode = $form->get('currency')->getViewData();

        return $orderExport->load($qb, $fieldsCsv, $currencyCode);
    }

    /**
     * @IsGranted("view_order")
     * @Route("/send/erp/{id}", name="send_erp", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function sendToErp(
        Order $order,
        OrderErpManagerInterface $orderErpManager
    )
    {
        if ($order->getStatus() == OrderStatuses::ERP_CREATING_ERROR && $order->getShop()->getIsWorking() ) {
            $erpId = $orderErpManager->createErpOrder($order);
            $order = $orderErpManager->saveErpOrder($order, $erpId);
            if ($order->getStatus() == OrderStatuses::ERP_CREATED || $order->getStatus() == OrderStatuses::TEST) {
                return $this->json(["result" => "ok",
                    "status" => OrderStatuses::MAP[$order->getStatus()],
                    "erpId" => $erpId], 200);
            }
        }
        return $this->json(['error' => '1'], 200);
    }
}
