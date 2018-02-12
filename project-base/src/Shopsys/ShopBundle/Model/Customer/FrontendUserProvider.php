<?php

namespace Shopsys\ShopBundle\Model\Customer;

use DateTime;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\ShopBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendUserProvider implements UserProviderInterface
{
    /**
     * @var \Shopsys\ShopBundle\Model\Customer\UserRepository
     */
    private $userRepository;

    /**
     * @var \Shopsys\ShopBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\ShopBundle\Model\Customer\UserRepository $userRepository
     * @param \Shopsys\ShopBundle\Component\Domain\Domain $domain
     */
    public function __construct(UserRepository $userRepository, Domain $domain)
    {
        $this->userRepository = $userRepository;
        $this->domain = $domain;
    }

    /**
     * @param string $email
     * @return \Shopsys\ShopBundle\Model\Customer\User
     */
    public function loadUserByUsername($email)
    {
        $user = $this->userRepository->findUserByEmailAndDomain(mb_strtolower($email), $this->domain->getId());

        if ($user === null) {
            $message = sprintf(
                'Unable to find an active Shopsys\ShopBundle\Model\Customer\User object identified by email "%s".',
                $email
            );
            throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException($message, 0);
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return \Shopsys\ShopBundle\Model\Customer\User
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            $message = sprintf('Instances of "%s" are not supported.', $class);
            throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException($message);
        }

        if ($user instanceof TimelimitLoginInterface) {
            if (time() - $user->getLastActivity()->getTimestamp() > 3600 * 24) {
                throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('User was too long unactive');
            }
            $user->setLastActivity(new DateTime());
        }

        if ($user instanceof UniqueLoginInterface) {
            $freshUser = $this->userRepository->findByIdAndLoginToken($user->getId(), $user->getLoginToken());
        } else {
            $freshUser = $this->userRepository->findById($user->getId());
        }

        if ($freshUser === null) {
            throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('Unable to find an active user');
        }

        return $freshUser;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}