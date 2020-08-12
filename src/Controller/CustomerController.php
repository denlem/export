<?php


namespace App\Controller;

use App\Entity\Customer;
use App\Form\Customer\CustomerFilterType;
use App\Form\Customer\ExportFieldsType;
use App\Service\Customer\CustomerQueryBuilder;
use App\Service\Export\ExportInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("customer_edit")
 * @Route("customers", name="customer_")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route(name="index", methods={"GET"})
     */
    public function index(
        PaginatorInterface $paginator,
        Request $request,
        CustomerQueryBuilder $customerQueryBuilder
    ) {
        $form = $this->createForm(CustomerFilterType::class)
            ->handleRequest($request);

        $exportForm = $this->createForm(ExportFieldsType::class)->handleRequest($request);

        $qb = $customerQueryBuilder->create($form);

        $page = $request->query->getInt('page', 1);

        $customers = $paginator->paginate($qb, $page, 20, [
            'defaultSortFieldName' => ['o.id'],
            'defaultSortDirection' => 'desc',
        ]);

        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
            'form' => $form->createView(),
            'exportForm' => $exportForm->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="view", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function view(int $id, EntityManagerInterface $entityManager)
    {
        $customer = $entityManager->find(Customer::class, $id);
        return $this->render('customer/view.html.twig', [
            'customer' => $customer
        ]);
    }

    /**
     * @Route("/export/csv", name="export_csv", methods={"GET", "POST"})
     */
    public function exportcsv(
        Request $request,
        CustomerQueryBuilder $customerQueryBuilder,
        ExportInterface $customerExport
    ) {
        $form = $this->createForm(CustomerFilterType::class)
            ->handleRequest($request);

        $qb = $customerQueryBuilder->create($form);

        $fieldsCsv = $form->get('csv_fields')->getData();

        return $customerExport->load($qb, $fieldsCsv);
    }
}