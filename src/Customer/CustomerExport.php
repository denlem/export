<?php


namespace App\Service\Customer;


use App\Constant\CustomerExportFields;
use App\Service\Export\ExportInterface;
use App\Service\Export\ExportManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class CustomerExport implements ExportInterface
{

    private array $fieldsDataCsv;
    private ExportManager $exportManager;

    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }

    public function load(QueryBuilder $qb, string $fieldsCsv): ?Response
    {
        $this->fieldsDataCsv = explode(",", $fieldsCsv);
//        $dataCustomers = $qb->select(array('o', 'c', 's'))
//                            ->leftJoin('o.carts', 'c')
//                            ->leftJoin('c.shop', 's')
        $dataCustomers = $qb->orderBy("o.id", "desc")
                            ->getQuery()->getArrayResult();

//        dd($dataCustomers);

        $dataCustomers = $this->formatDataFields($dataCustomers);
        return $this->exportManager->export($dataCustomers);
    }

    public function formatDataFields(array $dataCustomers): array
    {
        $newDataCustomers = [];
        $i=0;
        foreach ($dataCustomers as $dataIndex => $dataItem) {
            foreach ($this->fieldsDataCsv as $k => $v) {
                if (!in_array($v, ['shops', 'address'])) {
                    $newDataCustomers[$i][CustomerExportFields::getLabel($v)] = $dataItem[$v];
                } else {
                    if ($v == 'address') {
                        $newDataCustomers[$i][CustomerExportFields::getLabel($v)] =
                            $dataItem['address1']." ".$dataItem['address2'];
                    }elseif ($v == 'shops') {
                        $ids_shop = [];
                        $customersShopsArray = [];
                        $customersShopsStr = "";
                        $carts = $dataItem['carts'];
                        if (count($carts) > 0) {
                            foreach ($carts as $cart) {
                                if (isset($cart['shop'])) {
                                    if (!in_array($cart['shop']['id'], $ids_shop)) {
                                        $customersShopsArray[] = $cart['shop']['id'] . " - " . $cart['shop']['name'] ?? "";
                                    }
                                    $ids_shop[] = $cart['shop']['id'];
                                }
                            }
                            if (count($customersShopsArray)>0) {
                                $customersShopsStr = implode("\r\n", $customersShopsArray);
                            }
                        }
                        $newDataCustomers[$i][CustomerExportFields::getLabel($v)] = $customersShopsStr;
                    }
                }
            }
            $i++;
        }
        return $newDataCustomers;
    }
}