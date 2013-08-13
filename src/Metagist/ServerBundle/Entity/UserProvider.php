<?php
namespace Metagist\ServerBundle\Entity;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityRepository;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

/**
 * User provider for Metagist. Works in combination with github oauth.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * the config key containing the admin accounts
     * @var string
     */
    const CONFIG_ADMIN_LIST = 'admins';
    
    /**
     * user reop
     * 
     * @var UserRepository
     */
    private $repo;
    
    /**
     * userprovider configuration
     * @var array
     */
    private $config;
    
    /**
     * Constructor.
     * 
     * @param \Doctrine\ORM\EntityManager $conn
     * @param array                       $config
     */
    public function __construct(EntityRepository $repo, array $config = array())
    {
        $this->repo   = $repo;
        $this->config = $config;
    }

    /**
     * Loads a user.
     * 
     * @param string $username
     * @return \Metagist\User
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        $user = $this->repo->findOneBy(array('username' => $username));
        
        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }
    
    /**
     * Checks username against a list of admin usernames.
     * 
     * @param string $username
     * @return string
     */
    protected function getRoleByUsername($username)
    {
        $adminList = isset($this->config[self::CONFIG_ADMIN_LIST]) ? $this->config[self::CONFIG_ADMIN_LIST] : '';
        $admins = explode(',', $adminList);
        array_map('trim', $admins);
        array_map('strtolower', $admins);
        
        if (in_array(strtolower($username), $admins)) {
            return User::ROLE_ADMIN;
        }
        
        return User::ROLE_USER;
    }
    
    /**
     * Creates and saves a new user.
     * 
     * @param array $response
     * @return User
     */
    public function createUserFromOauthResponse(array $response)
    {
        $rawData = $response['auth']['raw'];
        $user = new User($rawData['login'], $this->getRoleByUsername($rawData['login']), $rawData['avatar_url']);
        
        try {
            $user = $this->loadUserByUsername($user->getUsername());
        } catch (UsernameNotFoundException $exception) {
            $stmt = $this->conn->executeQuery(
                'INSERT INTO users (username, avatar_url) VALUES (?, ?)',
                array($user->getUsername(), $user->getAvatarUrl())
            );
            $user->setId($this->conn->lastInsertId());
            if (!$stmt->rowCount()) {
                throw new \RuntimeException('Could not create the user.', 500, $exception);
            }
        }
        
        return $user;
    }

    /**
     * Refresh.
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return User
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Check is a class is supported by the provider.
     * 
     * @param string $class
     * @return boolean
     */
    public function supportsClass($class)
    {
        return $class === 'Metagist\ServerBundle\Entity\User';
    }
    
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        
    }

}