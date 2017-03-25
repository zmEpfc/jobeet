<?php

// src Ens/JobeetBundle/Repository/JobRepository.php

namespace Ens\JobeetBundle\Repository;

use Doctrine\ORM\EntityRepository;

class JobRepository extends EntityRepository
{

    public function getActiveJobs($category_id = null, $max = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('j')
                ->where('j.expires_at > :date')
                ->setParameter('date', date('Y-m-d H:i:s', time()))
                ->andWhere('j.isActivated = :activated')
                ->setParameter('activated', 1)
                ->orderBy('j.expires_at', 'DESC');

        if ($max)
        {
            $qb->setMaxResults($max);
        }

        if ($offset)
        {
            $qb->setFirstResult($offset);
        }

        if ($category_id)
        {
            $qb->andWhere('j.category = :category_id')
                    ->setParameter('category_id', $category_id);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function countActiveJobs($category_id = null)
    {
        $qb = $this->createQueryBuilder('j')
                ->select('count(j.id)')
                ->where('j.expires_at > :date')
                ->setParameter('date', date('Y-m-d H:i:s', time()))
                ->andWhere('j.isActivated = :activated')
                ->setParameter('activated', 1);

        if ($category_id)
        {
            $qb->andWhere('j.category = :category_id')
                    ->setParameter('category_id', $category_id);
        }

        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getActiveJob($id)
    {
        $query = $this->createQueryBuilder('j')
                ->where('j.id = :id')
                ->setParameter('id', $id)
                ->andWhere('j.expires_at > :date')
                ->setParameter('date', date('Y-m-d H:i:s', time()))
                ->andWhere('j.isActivated = :activated')
                ->setParameter('activated', 1)
                ->setMaxResults(1)
                ->getQuery();

        try
        {
            $job = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e)
        {
            $job = null;
        }

        return $job;
    }
}
