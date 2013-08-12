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
     * @param \Metagist\Package $package
     * @param \Metagist\User    $user
     * @return Rating|null
     */
    public function byPackageAndUser(Package $package, User $user)
    {
        return $this->findOneBy(array('package' => $package, 'user' => $user));
        
        $stmt = $this->connection->executeQuery(
            'SELECT r.*, u.id AS user_id, u.username, u.avatar_url, p.identifier, p.description
             FROM ratings r
             LEFT JOIN packages p ON r.package_id = p.id
             LEFT JOIN users u ON r.user_id = u.id
             WHERE package_id = ? AND r.user_id = ?',
             array($package->getId(), $user->getId())
        );
        $data = $stmt->fetch();
        if ($data != false) {
            $data['user']    = $user;
            $data['package'] = $package;
            return Rating::fromArray($data);
        }
        
        return null;
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
            
        return $builder->getQuery()->execute();
        $collection = new ArrayCollection();
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
        $package = $rating->getPackage();
        $userId  = $rating->getUserId();
        
        if ($package == null || $package->getId() == null || $userId == null) {
            throw new \RuntimeException('Package ID and User ID must be set.');
        }
        $packageId = $package->getId();
        
        $this->connection->executeQuery(
            'DELETE FROM ratings WHERE (package_id = ? AND user_id = ?)',
            array($packageId, $userId)
        );
        
        $data = array(
            $packageId,
            $userId,
            date('Y-m-d H:i:s', time()),
            $rating->getVersion(),
            $rating->getRating(),
            $rating->getTitle(),
            $rating->getComment()
        );
        
        $stmt = $this->connection->executeQuery(
            'INSERT INTO ratings (package_id, user_id, time_updated, version, rating, title, comment) 
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            $data
        );
        $rows = $stmt->rowCount();
        return $rows;
    }
    
    /**
     * Retrieves metainfo that has been updated lately.
     * 
     * @param int $limit
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @todo parameter binding did not work.
     */
    public function best($limit = 25)
    {
        $sql =
            'SELECT AVG(r.rating) AS rating, r.package_id, p.identifier, p.description
             FROM ratings r 
             LEFT JOIN packages p ON p.id = r.package_id
             GROUP BY r.package_id
             ORDER BY rating DESC LIMIT ' . $limit
        ;
        
        $builder = $this->createQueryBuilder('r')
            ->select('avg(r.rating) AS rateavg')
            ->join('MetagistServerBundle:Package', 'p')
            //->groupBy('r.package_id')
            //->orderBy('p.id', 'DESC')
            ;
        
        return $builder->getQuery()->execute();
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