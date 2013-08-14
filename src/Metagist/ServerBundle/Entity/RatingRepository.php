<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Repository for package ratings.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class RatingRepository extends EntityRepository
{
    /**
     * Retrieves all stored meta info for the given package.
     * 
     * @param \Metagist\Package $package
     * @param integer           $offset
     * @param integer           $limit
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byPackage(Package $package, $offset = 0, $limit = 25)
    {
        $builder = $this->createQueryBuilder('r')
            ->where('r.package = ?1')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        
        return $builder->getQuery()->execute(array(1 => $package));
    }
    
    /**
     * Retrieves the rating of a package by the given user.
     * 
     * @param Package $package
     * @param User    $user
     * @return Rating|null
     */
    public function byPackageAndUser(Package $package, User $user)
    {
        return $this->findOneBy(array('package' => $package, 'user' => $user));
    }
    
    /**
     * Retrieve the latest ratings.
     * 
     * @param int $limit
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function latest($limit = 1)
    {
        $builder = $this->createQueryBuilder('r')
            ->orderBy('r.timeUpdated', 'DESC')
            ;
            
        return new ArrayCollection($builder->getQuery()->execute());
        /*$stmt = $this->connection->executeQuery(
            'SELECT r.*, u.id AS user_id, u.username, u.avatar_url, p.identifier, p.description
             FROM ratings r
             LEFT JOIN packages p ON r.package_id = p.id
             LEFT JOIN users u ON r.user_id = u.id
             ORDER BY time_updated DESC LIMIT ' . (int)$limit,
            array()
        );
        while ($row = $stmt->fetch()) {
            $collection->add($this->createRatingWithDummyPackage($row));
        }
        
        return $collection;*/
    }
    
    /**
     * Saves (inserts) a single info.
     * 
     * @param \Metagist\Rating $rating
     * @return int
     */
    public function save(Rating $rating)
    {
        $this->getEntityManager()->persist($rating);
    }
    
    /**
     * Retrieves metainfo that has been updated lately.
     * 
     * @param int $limit
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function best($limit = 25)
    {
        $builder = $this->createQueryBuilder('r')
            ->select('avg(r.rating) AS rateavg')
            ->join('r.package', 'p')
            ->groupBy('p.id')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit);
        
        return new ArrayCollection($builder->getQuery()->execute());
    }
    
    /**
     * Creates a Rating instance with a dummy package based on the results
     * of a joined query.
     * 
     * @param array $data
     * @return Rating
     */
    private function createRatingWithDummyPackage(array $data)
    {
        $package = new Package($data['identifier'], $data['package_id']);
        if (isset($data['description'])) {
            $package->setDescription($data['description']);
        }
        $data['package'] = $package;
        
        if (isset($data['username'])) {
            $user = new User($data['username'], 'ROLE_USER', $data['avatar_url']);
            $user->setId($data['user_id']);
            $data['user'] = $user;
        }
        
        $rating = Rating::fromArray($data);
        return $rating;
    }
}