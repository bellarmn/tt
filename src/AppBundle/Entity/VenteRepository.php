<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Vente;

/**
 * VenteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VenteRepository extends \Doctrine\ORM\EntityRepository {

    public function getCountVente() {
        return $this->createQueryBuilder('v')
                        ->select('COUNT(v)')
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    private function getVentesQueryBuilder() {
        return $this->createQueryBuilder('v')
        ;
    }

    /**
     * Retourne les offres par catégory
     * @param type $category
     * @param type $limit
     * @param type $offset
     * @param type $sortedBy
     * @return type
     */
    public function getVentesByCategory($category, $limit = null, $offset = null, $sortedBy = null) {

        $query = $this->getVentesQueryBuilder()
                ->innerJoin('v.product', 'p')
                ->innerJoin('p.category', 'c')
                ->where("c.name LIKE :category")
                ->setParameter('category', '%' . $category . '%');

        return $this->getQueryResult($query, $limit, $offset, $sortedBy);
    }

    public function getVentesByCategoryId($category, $limit = null, $offset = null, $sortedBy = null) {

        $query = $this->getVentesQueryBuilder()
                ->innerJoin('v.product', 'p')
                ->innerJoin('p.category', 'c')
                ->where("c.id =:category")
                ->setParameter('category', $category);

        return $this->getQueryResult($query, $limit, $offset, $sortedBy);
    }

    private function getQueryResult($query, $limit, $offset, $sortedBy) {
        if ($limit && $offset) {
            $query->setMaxResults($limit)
                    ->setFirstResult($offset);
        }

        if (!$sortedBy) {
            $query->addOrderBy('v.createAt', 'DESC');
        } else {
            if ($sortedBy == 'product') {
                $query->innerJoin('v.product', 'p');
                $query->addOrderBy("p", 'ASC');
            } else {
                $query->addOrderBy("v.{$sortedBy}", 'DESC');
            }
        }
        $query->andwhere('v.published  =:published')
                ->setParameter('published', true);
        return $query->getQuery()
                        ->getResult();
    }

    public function getVentes($limit = null, $offset = null, $sortedBy = null) {

        $query = $this->getVentesQueryBuilder();
        return $this->getQueryResult($query, $limit, $offset, $sortedBy);
    }

    public function getForLuceneQuery($query) {
        $hits = Vente::getLuceneIndex()->find($query);

        $pks = array();
        foreach ($hits as $hit) {
            $pks[] = $hit->pk;
        }

        if (empty($pks)) {
            return array();
        }

        $q = $this->createQueryBuilder('v')
                ->where('v.id IN (:pks)')
                ->setParameter('pks', $pks)
//            ->andWhere('v.is_activated = :active')
//            ->setParameter('active', 1)
                ->setMaxResults(20)
                ->getQuery();

        return $q->getResult();
    }

    /**
     * Retourne les offres par produit
     * @param type $product
     * @param type $limit
     * @param type $offset
     * @param type $sortedBy
     * @return type
     */
    public function getVentesByProduct($product, $limit = null, $offset = null, $sortedBy = null) {

        $query = $this->getVentesQueryBuilder()
                ->innerJoin('v.product', 'p')
                ->where("p.name LIKE :product")
                ->setParameter('product', '%' . $product . '%');

        return $this->getQueryResult($query, $limit, $offset, $sortedBy);
    }

    /**
     * Retourne les offres relatives à un produit
     * @param type $product
     * @param type $limit
     * @param type $offset
     * @param type $sortedBy
     * @return type
     */
    public function getVentesByProductId($product, $limit = null, $offset = null, $sortedBy = null) {

        $query = $this->getVentesQueryBuilder()
                ->innerJoin('v.product', 'p')
                ->where("p.id =:product")
                ->setParameter('product', $product);

        return $this->getQueryResult($query, $limit, $offset, $sortedBy);
    }

    public function getVentesByCityProductId($cityId, $productId, $limit = null, $offset = null, $sortedBy = null) {
        $query = $this->getVentesQueryBuilder()
                ->innerJoin('v.product', 'p')
                ->innerJoin('v.district', 'd')
                ->innerJoin('d.city', 'c')
                ->where("c.id =:cityId")
                ->andwhere("p.id =:productId")
                ->setParameter('cityId', $cityId)
                ->setParameter('productId', $productId);

        return $this->getQueryResult($query, $limit, $offset, $sortedBy);
    }

    public function getVentesByCityId($cityId, $limit = null, $offset = null, $sortedBy = null) {
        $query = $this->getVentesQueryBuilder()
                ->innerJoin('v.district', 'd')
                ->innerJoin('d.city', 'c')
                ->where("c.id =:id")
                ->setParameter('id', $cityId);

        return $this->getQueryResult($query, $limit, $offset, $sortedBy);
    }

    //les actions de dashboard
    public function getDashboardCountBrouillons($user) {
        return $this->createQueryBuilder('v')
                        ->select('COUNT(v)')
                        ->where('v.user  =:user')
                        ->andwhere('v.published  =:published')
                        ->andwhere('v.deleted  =:deleted')
                        ->andwhere('v.canceled  =:canceled')
                        ->andwhere('v.available  =:available')
                        ->setParameter('user', $user)
                        ->setParameter('published', false)
                        ->setParameter('available', true)
                        ->setParameter('deleted', false)
                        ->setParameter('canceled', false)
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    public function getDashboardCountPulibes($user) {
        return $this->createQueryBuilder('v')
                        ->select('COUNT(v)')
                        ->where('v.user  =:user')
                        ->andwhere('v.published  =:published')
                        ->andwhere('v.deleted  =:deleted')
                        ->andwhere('v.canceled  =:canceled')
                        ->andwhere('v.available  =:available')
                        ->setParameter('user', $user)
                        ->setParameter('published', true)
                        ->setParameter('available', true)
                        ->setParameter('deleted', false)
                        ->setParameter('canceled', false)
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    public function getDashboardCountResolus($user) {
        return $this->createQueryBuilder('v')
                        ->select('COUNT(v)')
                        ->where('v.user  =:user')
                        ->andwhere('v.published  =:published')
                        ->andwhere('v.deleted  =:deleted')
                        ->andwhere('v.canceled  =:canceled')
                        ->andwhere('v.available  =:available')
                        ->setParameter('user', $user)
                        ->setParameter('published', false)
                        ->setParameter('available', false)
                        ->setParameter('deleted', false)
                        ->setParameter('canceled', false)
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    public function getDashboardCountCorbeille($user) {
        return $this->createQueryBuilder('v')
                        ->select('COUNT(v)')
                        ->where('v.user  =:user')
                        ->andwhere('v.published  =:published')
                        ->andwhere('v.deleted  =:deleted')
                        ->andwhere('v.canceled  =:canceled')
                        ->andwhere('v.available  =:available')
                        ->setParameter('user', $user)
                        ->setParameter('published', false)
                        ->setParameter('available', true)
                        ->setParameter('deleted', false)
                        ->setParameter('canceled', true)
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    public function getDashboardCountExpires($user) {
        return $this->createQueryBuilder('v')
                        ->select('COUNT(v)')
                        ->where('v.user  =:user')
                        // ->andwhere('v.published  =:published')
                        ->andwhere('v.deleted  =:deleted')
                        ->andwhere('v.canceled  =:canceled')
                        ->andwhere('v.available  =:available')
                        ->andwhere('v.dateLimit  <= :datedujour')
                        ->orWhere('v.dateLimitUpdate  <= :datedujour')
                        ->setParameter('user', $user)
                        // ->setParameter('published', false)
                        ->setParameter('available', true)
                        ->setParameter('deleted', false)
                        ->setParameter('canceled', false)
                        ->setParameter('datedujour', new \DateTime())
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    public function getDashboardExpires($user) {
        return $this->createQueryBuilder('v')
                        ->where('v.user  =:user')
                        // ->andwhere('v.published  =:published')
                        ->andwhere('v.deleted  =:deleted')
                        ->andwhere('v.canceled  =:canceled')
                        ->andwhere('v.available  =:available')
                        ->andwhere('v.dateLimit  <= :datedujour')
                        ->orWhere('v.dateLimitUpdate  <= :datedujour')
                        ->setParameter('user', $user)
                        // ->setParameter('published', false)
                        ->setParameter('available', true)
                        ->setParameter('deleted', false)
                        ->setParameter('canceled', false)
                        ->setParameter('datedujour', new \DateTime())
                        ->getQuery()->getResult();
    }

     public function getDashboardCountMarket($user) {
        return $this->createQueryBuilder('v')
                        ->select('COUNT(v)')
                        ->where('v.user  =:user')
                        ->andwhere('v.deleted  =:deleted')
                        ->andwhere('v.canceled  =:canceled')
                        ->setParameter('user', $user)
                        ->setParameter('deleted', false)
                        ->setParameter('canceled', false)
                        ->getQuery()
                        ->getSingleScalarResult();
    }
  
    
}
