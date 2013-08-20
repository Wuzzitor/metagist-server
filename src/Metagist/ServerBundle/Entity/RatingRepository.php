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
     * @param \Metagist\ServerBundle\Entity\Package $package
     * @param integer           $offset
     * @param integer           $limit
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byPackage(Package $package, $offset = 0, $limit = 25)
    {
        if ($package->getId() === null) {
            throw new \RuntimeException('Package has no id.');
        }
        $builder = $this->createQueryBuilder('r')
            ->where('r.package = :package')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        
        return new ArrayCollection($builder->getQuery()->execute(array('package' => $package)));
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
            ->setMaxResults($limit);
        
        return new ArrayCollection($builder->getQuery()->execute());
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
        $this->getEntityManager()->flush();
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
            ->select('avg(r.rating) rateavg, r')
            ->join('r.package', 'p')
            ->groupBy('p.id')
            ->orderBy('rateavg', 'DESC')
            ->setMaxResults($limit);
        
        $result = $builder->getQuery()->execute();
        $collection = new ArrayCollection();
        foreach ($result as $data) {
            foreach ($data as $entry) {
                if ($entry instanceof Rating) {
                    $collection->add($entry->getPackage());
                }
            }
        }
        return $collection;
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