<?php


namespace App\Service\Order;

use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\QueryBuilder;

class OrderCurrencyManager
{

    private $qbCurrency;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->qbCurrency = $currencyRepository->createQueryBuilder('c');
    }

    public function getOrderSumm(?string $currencyCode, QueryBuilder $qb): array
    {
        $sumOrder = $currencyRate = 0;
        $currency = $currencyData = null;
        if ($currencyCode !== null && $currencyCode!=''){
            $currencyData = $this->qbCurrency
                ->where('c.code = :code')
                ->setParameter('code', $currencyCode)
                ->getQuery()->getSingleResult();
        }
        if ($currencyData !== null && $currencyData->getRate() !== null) {
            $orderItems = $qb->getQuery()->getResult();
            $sumOrderUno = 0;
            $currencyRate = $currencyData->getRate();
            foreach ($orderItems as $order) {
                $currentTotal = $order->getTotal();
                $shop = $order->getShop();

                if ($shop !== null && $shop->getId() != null) {
                    $currentCurrency = $order->getShop()->getCurrency();
                    if ($currentTotal > 0 &&
                        $currentCurrency !== null &&
                        $currentCurrency->getRate() !== null &&
                        $currentCurrency->getRate() > 0) {
                        $sumOrderUno += $currentTotal / $currentCurrency->getRate();
                    }
                }
            }
            if ($sumOrderUno > 0) {
                $sumOrder = round($sumOrderUno * $currencyRate, 2);
            }
            $currency = $currencyData->getCode();
        }
        return ['summ' => $sumOrder, 'currency'=>$currency, 'rate'=>$currencyRate];
    }
}
