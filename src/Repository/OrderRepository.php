<?php

namespace App\Repository;

use App\Constant\OrderStatuses;
use App\Constant\Roles;
use App\Entity\AbTestShop;
use App\Entity\Order;
use App\Entity\PaymentType;
use App\Helpers\DateHelper;
use App\Service\Shop\ShopService;
use App\Service\Statistics\StatisticsService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use PDO;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param string $field
     * @param string $customParamName
     * @param $value
     * @param string $sign
     * @return $this
     */
    public function filter(string $field, $value, string $sign = '=', ?string $customParamName = null)
    {
        $customParamName = $customParamName ?? $field;

        $qb = $this->getQueryBuilder();
        if ($value != null) {
            $qb->andWhere('t.' . $field . $sign . ':' . $customParamName)
                ->setParameter(':' . $customParamName, $value);
        }

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        if ($this->queryBuilder === null) {
            $this->queryBuilder = $this->createQueryBuilder('t');
        }

        return $this->queryBuilder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param int $interval
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getStatistics(\DateTime $from, \DateTime $to, int $interval, array $params)
    {
        $data = $this->getRawStatistics($from, $to, $interval, $params);
        $hours = DateHelper::getHours($from, $to, $interval);

        $datasets = StatisticsService::prepareStatisticsData($data, $hours, 'orders');

        return [
            'labels' => $hours,
            'datasets' => $datasets
        ];
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param $interval
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getRawStatistics(\DateTime $from, \DateTime $to, $interval, array $params)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT COUNT(o.id) as orders, MAX(s.name) as name, (date_trunc(\'day\', o.created_at) + (floor(extract(hour from o.created_at) / '
            . $interval . ') * interval \'' . $interval . ' hour\')) as hour '
            . ' FROM orders o LEFT OUTER JOIN shops s ON o.shop_id=s.id'
            . ' WHERE ';

        if ($params['user'] && $params['user']->getRoles()[0] == Roles::ORDER_MANAGER) {
            $shopIds = ShopService::getIdsFromCollection($params['user']->getShops());
            $sql .= ' s.id IN (' . implode(',', $shopIds) . ') AND ';
        }

        $sql .= 'o.created_at BETWEEN \'' . $from->format('Y-m-d H:i:s') . '\' AND \'' . $to->format('Y-m-d H:i:s') . '\''
        . ' GROUP BY hour, o.shop_id ORDER BY hour DESC;';

        return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     * @return QueryBuilder
     */
    public function forUser(int $id)
    {
        return $this->getQueryBuilder()
            ->leftJoin('t.shop', 's')
            ->leftJoin('s.managers', 'm')
            ->where('m.id = :userId')
            ->setParameter(':userId', $id);
    }

    public function findPartForPaymentCreating(int $partSize, ?int $prevLastId = null): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.status = :paymentCreatingStatus');
        $qb->orderBy('o.id', 'asc');
        $qb->setMaxResults($partSize);
        $qb->setParameter('paymentCreatingStatus', OrderStatuses::WAITING_PAYMENT);

        if (is_int($prevLastId)) {
            $qb->andWhere('o.id > :prevLastId');
            $qb->setParameter('prevLastId', $prevLastId);
        }

        return $qb->getQuery()->getResult();
    }

    public function getAbTestNRPStatsOrders(
        AbTestShop $abTestShop,
        array $nomenclatureIds,
        ?PaymentType $paymentType = null
    ): array
    {
        $params = [
            'abTestId' => '[' . $abTestShop->getAbTest()->getId() . ']',
            'shopId' => $abTestShop->getShop()->getId(),
            'nomenclatureIds' => $nomenclatureIds,
        ];

        if ($paymentType instanceof PaymentType) {
            $paymentTypeCondition = ' AND o.payment_method = :paymentMethod';
            $params['paymentMethod'] = $paymentType->getCode();
        } else {
            $paymentTypeCondition = '';
        }

        $sql = <<<SQL
SELECT t.nomenclature_id, t.price_orig AS price, COUNT(t.id) AS orders, SUM(t.items_price) AS items_price, SUM(t.total) AS total_price
FROM (
	SELECT o.id, o.items_price, o.total, oi.nomenclature_id, oi.price_orig, row_number() OVER (PARTITION BY o.id ORDER BY o.id, oi.id) as num
	FROM orders o
	INNER JOIN order_items oi ON oi.order_id = o.id AND oi.nomenclature_id IN (:nomenclatureIds)
	WHERE (o.ab_test_ids)::jsonb @> :abTestId::jsonb AND o.shop_id = :shopId{$paymentTypeCondition}
) t
WHERE t.num = 1
GROUP BY t.nomenclature_id, t.price_orig
SQL;

        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params, [
                'nomenclatureIds' => Connection::PARAM_INT_ARRAY
            ])
            ->fetchAll();
    }

    public function getAbTestNRPStatsProductQuantity(
        AbTestShop $abTestShop,
        array $nomenclatureIds,
        ?PaymentType $paymentType = null
    ): int
    {
        $params = [
            'abTestId' => '[' . $abTestShop->getAbTest()->getId() . ']',
            'shopId' => $abTestShop->getShop()->getId(),
            'nomenclatureIds' => $nomenclatureIds,
        ];

        if ($paymentType instanceof PaymentType) {
            $paymentTypeCondition = ' AND o.payment_method = :paymentMethod';
            $params['paymentMethod'] = $paymentType->getCode();
        } else {
            $paymentTypeCondition = '';
        }

        $sql = <<<SQL
SELECT SUM(oi.quantity) AS quantity
FROM (
	SELECT o.id AS order_id, oi.nomenclature_id, row_number() OVER (PARTITION BY o.id) as num
	FROM orders o
	INNER JOIN order_items oi ON oi.order_id = o.id
	WHERE (o.ab_test_ids)::jsonb @> :abTestId::jsonb AND o.shop_id = :shopId{$paymentTypeCondition}
) t
JOIN order_items oi ON oi.order_id = t.order_id
WHERE t.num = 1 AND t.nomenclature_id IN (:nomenclatureIds)
SQL;

        return (int)$this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params, [
                'nomenclatureIds' => Connection::PARAM_INT_ARRAY
            ])
            ->fetchColumn();
    }

    public function getAbTestPTStatsOrders(AbTestShop $abTestShop, ?PaymentType $paymentType = null): array
    {
        $params = [
            'abTestId' => '[' . $abTestShop->getAbTest()->getId() . ']',
            'shopId' => $abTestShop->getShop()->getId(),
        ];

        if ($paymentType instanceof PaymentType) {
            $paymentTypeCondition = ' AND o.payment_method = :paymentMethod';
            $params['paymentMethod'] = $paymentType->getCode();
        } else {
            $paymentTypeCondition = '';
        }

        $sql = <<<SQL
SELECT
    o.ab_test_payment_type_list_index AS list_index,
    COUNT(o.id) AS orders,
    SUM(o.items_price) AS items_price,
    SUM(o.total) AS total_price
FROM orders o
WHERE (o.ab_test_ids)::jsonb @> :abTestId::jsonb AND o.shop_id = :shopId{$paymentTypeCondition}
GROUP BY o.ab_test_payment_type_list_index
SQL;

        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params)
            ->fetchAll();
    }

    public function getAbTestPTStatsProductQuantity(AbTestShop $abTestShop, ?PaymentType $paymentType = null): int
    {
        $params = [
            'abTestId' => '[' . $abTestShop->getAbTest()->getId() . ']',
            'shopId' => $abTestShop->getShop()->getId(),
        ];

        if ($paymentType instanceof PaymentType) {
            $paymentTypeCondition = ' AND o.payment_method = :paymentMethod';
            $params['paymentMethod'] = $paymentType->getCode();
        } else {
            $paymentTypeCondition = '';
        }

        $sql = <<<SQL
SELECT SUM(oi.quantity) AS quantity
FROM orders o
INNER JOIN order_items oi ON oi.order_id = o.id
WHERE (o.ab_test_ids)::jsonb @> :abTestId::jsonb AND o.shop_id = :shopId{$paymentTypeCondition}
SQL;

        return (int)$this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params)
            ->fetchColumn();
    }

    public function filterIn(string $field, ?array $values)
    {
        $qb = $this->getQueryBuilder();

        if ($values) {
            $qb->andWhere($qb->expr()->in('t.' . $field, $values));
        }

        return $this;
    }
}
