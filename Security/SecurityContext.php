<?php
namespace Crocos\SecurityBundle\Security;

use Crocos\SecurityBundle\Annotation\Secure;
use Crocos\SecurityBundle\Security\AuthLogic\AuthLogicInterface;
use Crocos\SecurityBundle\Security\AuthLogic\RolePreloadableInterface;
use Crocos\SecurityBundle\Security\HttpAuth\HttpAuthInterface;
use Crocos\SecurityBundle\Security\Role\RoleManagerInterface;

class SecurityContext
{
    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var array
     */
    protected $allowedRoles = [];

    /**
     * @var string
     */
    protected $domain = 'secured';

    /**
     * @var bool
     */
    protected $domainFixed = false;

    /**
     * @var bool
     */
    protected $httpsRequired;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $forwardingController;

    /**
     * @var AuthLogicInterface
     */
    protected $authLogic;

    /**
     * @var HttpAuthInterface[]
     */
    protected $httpAuths = [];

    /**
     * @var RoleManagerInterface
     */
    protected $roleManager;

    /**
     * @var PreviousUrlHolder
     */
    protected $previousUrlHolder;

    /**
     * Set secure.
     *
     * @param bool $security
     */
    public function setSecure($security)
    {
        $this->secure = $security;
    }

    /**
     * Check is secure.
     *
     * @return bool
     */
    public function isSecure()
    {
        return (bool) $this->secure;
    }

    /**
     * Set allowed roles.
     *
     * @param array $roles
     */
    public function setAllowedRoles(array $roles)
    {
        $this->allowedRoles = $roles;
    }

    /**
     * Get allowed roles.
     *
     * @return array
     */
    public function getAllowedRoles()
    {
        return $this->allowedRoles;
    }

    /**
     * Check has all allowed roles?
     *
     * @return bool
     */
    public function hasAllowedRoles()
    {
        return $this->hasRole($this->getAllowedRoles());
    }

    /**
     * Set security domain name.
     *
     * @param string domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Get security domain name.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set httpsRequired.
     *
     * @param bool httpsRequired
     */
    public function setHttpsRequired($httpsRequired)
    {
        $this->httpsRequired = (bool) $httpsRequired;
    }

    /**
     * Get httpsRequired.
     *
     * @return bool
     */
    public function getHttpsRequired()
    {
        return $this->httpsRequired;
    }

    /**
     * Is httpsRequired.
     *
     * @return bool
     */
    public function isHttpsRequired()
    {
        return $this->getHttpsRequired();
    }

    /**
     * Set secure options.
     *
     * @param array|string $options
     */
    public function setOptions($options)
    {
        $this->options = (array) $options;
    }

    /**
     * Get secure options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set login controller.
     *
     * @param mixed $controller
     */
    public function setForwardingController($controller)
    {
        $this->forwardingController = $controller;
    }

    /**
     * Get login controller.
     *
     * @return mixed
     */
    public function getForwardingController()
    {
        return $this->forwardingController;
    }

    /**
     * Fix domain.
     */
    public function fixDomain()
    {
        if (null !== $this->authLogic) {
            $this->authLogic->setDomain($this->domain);
        }

        if (null !== $this->roleManager) {
            $this->roleManager->setDomain($this->domain);
        }

        if (null !== $this->previousUrlHolder) {
            $this->previousUrlHolder->setup($this->domain);
        }

        $this->domainFixed = true;
    }

    /**
     * @return bool
     */
    public function isDomainFixed()
    {
        return $this->domainFixed;
    }

    /**
     * Log in.
     *
     * @param mixed $user
     */
    public function login($user)
    {
        if (null === $this->authLogic) {
            throw new \LogicException('Login error: No auth logic');
        }

        $this->authLogic->login($user);
    }

    /**
     * Log out.
     */
    public function logout()
    {
        if (null === $this->authLogic) {
            throw new \LogicException('Logout error: No auth logic');
        }

        $this->roleManager->clearRoles();

        $this->authLogic->logout();
    }

    /**
     * Check is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        if (null === $this->authLogic) {
            return false;
        }

        return $this->authLogic->isAuthenticated();
    }

    /**
     * Get user.
     *
     * @return mixed
     */
    public function getUser()
    {
        if (null === $this->authLogic) {
            return;
        }

        return $this->authLogic->getUser();
    }

    /**
     * Use http auth.
     *
     * @return bool
     */
    public function useHttpAuth()
    {
        return count($this->httpAuths) > 0;
    }

    /**
     * Enable http auth.
     *
     * @param HttpAuthInterface $httpAuth
     */
    public function enableHttpAuth($name, HttpAuthInterface $httpAuth)
    {
        $this->httpAuths[$name] = $httpAuth;
    }

    /**
     * Get http auth.
     *
     * @param string $name
     *
     * @return HttpAuthInterface
     */
    public function getHttpAuth($name)
    {
        if (!isset($this->httpAuths[$name])) {
            throw new \InvalidArgumentException(sprintf('Unknown http auth "%s"', $name));
        }

        return $this->httpAuths[$name];
    }

    /**
     * Get http auth array.
     *
     * @return HttpAuthInterface[]
     */
    public function getHttpAuths()
    {
        return $this->httpAuths;
    }

    /**
     * Has role.
     *
     * @param array|string $roles
     */
    public function hasRole($roles)
    {
        $this->preloadRoles();

        return $this->roleManager->hasRole($roles);
    }

    /**
     * Set roles.
     *
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roleManager->setRoles($roles);
    }

    /**
     * Add roles.
     *
     * @param array $roles
     */
    public function addRoles($roles)
    {
        $this->roleManager->addRoles($roles);
    }

    /**
     * Get roles.
     *
     * @return array
     */
    public function getRoles()
    {
        $this->preloadRoles();

        return $this->roleManager->getRoles();
    }

    /**
     * Clear roles.
     */
    public function clearRoles()
    {
        $this->roleManager->clearRoles();
    }

    /**
     * @see PreviousUrlHolder
     */
    public function hasPreviousUrl()
    {
        if (null === $this->previousUrlHolder) {
            return false;
        }

        return $this->previousUrlHolder->has();
    }

    /**
     * @see PreviousUrlHolder
     */
    public function setPreviousUrl($url)
    {
        if (null === $this->previousUrlHolder) {
            return;
        }

        $this->previousUrlHolder->set($url);
    }

    /**
     * @see PreviousUrlHolder
     */
    public function getPreviousUrl()
    {
        if (null === $this->previousUrlHolder) {
            return;
        }

        return $this->previousUrlHolder->get();
    }

    /**
     * Set authentication/authorization logic.
     *
     * @param AuthLogicInterface $authLogic
     */
    public function setAuthLogic(AuthLogicInterface $authLogic)
    {
        $this->authLogic = $authLogic;
    }

    /**
     * Get authentication/authorization logic.
     *
     * @return AuthLogicInterface
     */
    public function getAuthLogic()
    {
        return $this->authLogic;
    }

    /**
     * Set role manager.
     *
     * @param RoleManagerInterface $roleManager
     */
    public function setRoleManager(RoleManagerInterface $roleManager)
    {
        $this->roleManager = $roleManager;
    }

    /**
     * Get role manager.
     *
     * @return RoleManagerInterface
     */
    public function getRoleManager()
    {
        return $this->roleManager;
    }

    /**
     * Set PreviousUrlHolder.
     *
     * @param PreviousUrlHolder $previousUrlHolder
     */
    public function setPreviousUrlHolder(PreviousUrlHolder $previousUrlHolder)
    {
        $this->previousUrlHolder = $previousUrlHolder;
    }

    /**
     * Get PreviousUrlHolder.
     *
     * @return PreviousUrlHolder
     */
    public function getPreviousUrlHolder()
    {
        return $this->previousUrlHolder;
    }

    protected function preloadRoles()
    {
        if ($this->authLogic !== null && $this->authLogic instanceof RolePreloadableInterface) {
            if ($this->authLogic->isRolePreloadable() && !$this->roleManager->isPreloaded()) {
                try {
                    $roles = $this->authLogic->preloadRoles();
                    $this->roleManager->addRoles($roles);

                    $this->roleManager->setPreloaded();
                } catch (\RuntimeException $e) {
                    // todo
                }
            }
        }
    }
}
