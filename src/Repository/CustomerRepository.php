<?php

namespace App\Repository;

use App\Constant\Roles;
use App\Helpers\DateHelper;
use App\Service\Shop\ShopService;
use App\Service\Statistics\StatisticsService;
use PDO;
use App\Entity\Customer;
use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findOneByShopAndOriginId(Shop $shop, int $originId): ?Customer
    {
        $qb = $this->getQueryBuilder();
        $qb->where('o.shop = :shop');
        $qb->andWhere('o.originId = :origin_id');
        $qb->setMaxResults(1);
        $qb->setParameter('origin_id', $originId);
        $qb->setParameter('shop',$shop);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        if ($this->queryBuilder === null) {
            $this->queryBuilder = $this->createQueryBuilder('o');
        }

        return $this->queryBuilder;
    }

    /**
     * @param string $field
     * @param $value
     * @param string|null $customParamName
     * @return $this
     */
    public function filterLike(string $field, $value, ?string $customParamName = null)
    {
        $customParamName = $customParamName ?? $field;
        $likePrefix = "%";
        $qb = $this->getQueryBuilder();
        if ($value != null ) {
            $qb->andWhere('lower(o.' . $field .") LIKE :" . $customParamName)
                ->setParameter( $customParamName, $likePrefix .  mb_strtolower($value) . $likePrefix);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @param string $sign
     * @param string|null $customParamName
     * @return $this
     */
    public function filter(string $field, $value, string $sign = '=', ?string $customParamName = null)
    {
        $customParamName = $customParamName ?? $field;

        $qb = $this->getQueryBuilder();
        if ($value != null) {
            $qb->andWhere('o.' . $field . $sign . ':' . $customParamName)
                ->setParameter(':' . $customParamName, $value);
        }

        return $this;
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

        $datasets = StatisticsService::prepareStatisticsData($data, $hours, 'customers');

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
        $sql = 'SELECT COUNT(c.id) as customers, MAX(s.name) as name, (date_trunc(\'day\', c.created_at) + (floor(extract(hour from c.created_at) / '
            . $interval . ') * interval \'' . $interval . ' hour\')) as hour '
            . ' FROM customers c LEFT OUTER JOIN shops s ON c.shop_id=s.id'
            . ' WHERE ';

        if ($params['user'] && $params['user']->getRoles()[0] == Roles::ORDER_MANAGER) {
            $shopIds = ShopService::getIdsFromCollection($params['user']->getShops());
            $sql .= ' s.id IN (' . implode(',', $shopIds) . ') AND ';
        }

        $sql .= 'c.created_at BETWEEN \'' . $from->format('Y-m-d H:i:s') . '\' AND \'' . $to->format('Y-m-d H:i:s') . '\''
            . ' GROUP BY hour, c.shop_id ORDER BY hour DESC;';

        return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

}
