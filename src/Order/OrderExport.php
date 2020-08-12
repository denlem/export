<?php


namespace App\Service\Order;


use App\Constant\OrderExportFields;
use App\Constant\OrderStatuses;
use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use App\Service\Export\ExportInterface;
use App\Service\Export\ExportManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class OrderExport implements ExportInterface
{
    private array $fieldsDataCsv;
    private ExportManager $exportManager;
    private QueryBuilder $qbCurrency;
    private ?Currency $currencyData = null;
    private string $currencyFieldName;

    public function __construct(ExportManager $exportManager, CurrencyRepository $currencyRepository)
    {
        $this->exportManager = $exportManager;
        $this->qbCurrency = $currencyRepository->createQueryBuilder('c');
    }

    /**
     * @param QueryBuilder $qb
     * @param string $fieldsCsv
     * @param string $currencyCode
     * @return Response|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function load(QueryBuilder $qb, string $fieldsCsv, string $currencyCode = null): ?Response
    {
        $this->fieldsDataCsv = explode(",", $fieldsCsv);
        $currency = null;
        if ($currencyCode !== null && $currencyCode!='') {
            $this->currencyData = $this->qbCurrency
                ->where('c.code = :code')
                ->setParameter('code', $currencyCode)
                ->getQuery()->getSingleResult();
            $this->currencyFieldName = 'В '.$this->currencyData->getCode();
        }
        $dataOrders = $qb->select(array('t', 's', 'c', 'oi', 'n', 'p'))
                         ->leftJoin('t.shop', 's')
                         ->leftJoin('t.orderItems', 'oi')
                         ->leftJoin('oi.nomenclature', 'n')
                         ->leftJoin('n.product', 'p')
                         ->leftJoin('s.currency', 'c')
                         ->orderBy("t.id", "desc")
                         ->getQuery()->getArrayResult();

        $dataOrders = $this->formatDataFields($dataOrders);
        return $this->exportManager->export($dataOrders);

    }

    public function formatDataFields(array $dataOrders): array
    {
        $newDataOrders = [];
        $i=0;
        foreach ($dataOrders as $dataIndex => $dataItem) {
            foreach ($this->fieldsDataCsv as $k => $v) {
                if (!in_array($v, ['shop_name', 'orderItems', 'shop_currency', 'address', 'createdAt', 'total', 'status'])) {
                    $newDataOrders[$i][OrderExportFields::getLabel($v)] = $dataItem[$v];
                } else {
                    if ($v == 'shop_name') {
                        $newDataOrders[$i][OrderExportFields::getLabel($v)] = $dataItem['shop']['name'];
                    } elseif ($v == 'status') {
                        $newDataOrders[$i][OrderExportFields::getLabel($v)] = OrderStatuses::MAP[$dataItem[$v]];
                    } elseif ($v == 'total') {
                        $newDataOrders[$i][OrderExportFields::getLabel($v)] = $dataItem[$v];
                        if ($this->currencyData !== null &&  $dataItem['shop']['currency'] != null
                            &&  $this->currencyData->getRate()!= null && $this->currencyData->getRate() > 0) {
                            $total_currency = $dataItem[$v] / $dataItem['shop']['currency']['rate']
                                * $this->currencyData->getRate();
                            $newDataOrders[$i][$this->currencyFieldName] = number_format($total_currency, 2, '.', ' ');
                        }
                    } elseif ($v == 'address') {
                        $newDataOrders[$i][OrderExportFields::getLabel($v)] =
                            $dataItem['address1']." ".$dataItem['address2'];
                    } elseif ($v == 'createdAt') {
                        $newDataOrders[$i][OrderExportFields::getLabel($v)] =
                            $dataItem[$v]->format('d.m.y H:i');
                    } elseif ($v == 'shop_currency') {
                        $getCurrency = $dataItem['shop']['currency'];
                        if ($getCurrency !== null) {
                            $newDataOrders[$i][OrderExportFields::getLabel($v)] =
                                $getCurrency['code'] ." (".$getCurrency['name'].")";
                        } else {
                            $newDataOrders[$i][OrderExportFields::getLabel($v)] = '-';
                        }
                    } elseif ($v == 'orderItems') {
                        $orderItemsArray = [];
                        $orderItemsStr = "";
                        $orderItems = $dataItem['orderItems'];
                        if (count($orderItems) > 0) {
                            foreach ($orderItems as $carItem) {
                                $product =  $carItem['nomenclature']['id']." - ".
                                    $carItem['nomenclature']['product']
                                        ['name'] ?? 'Неизвестный продукт';
                                $product .= " (цена ".$carItem['price'].", кол-во: ".$carItem['quantity'].")";
                                $orderItemsArray[] = $product;
                            }
                            if (count($orderItemsArray)>0) {
                                $orderItemsStr = implode("\r\n", $orderItemsArray);
                            }
                        }
                        $newDataOrders[$i][OrderExportFields::getLabel($v)] = $orderItemsStr;
                    }
                }
            }
            $i++;
        }
        return $newDataOrders;
    }
}