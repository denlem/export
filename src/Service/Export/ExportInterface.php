<?php


namespace App\Service\Export;


use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

interface ExportInterface
{
    public function load(QueryBuilder $qb, string $fieldsCsv): ?Response;
}