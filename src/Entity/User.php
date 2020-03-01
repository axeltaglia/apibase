<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user");
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository");
 */
class User extends AbstractEntity implements UserInterface
{
    /**
     *
     * @ORM\Column(name="name", type="string", length=150)
     */
    protected $name;

    /**
     *
     * @ORM\Column(name="surname", type="string", length=150)
     */
    protected $surname;

    /**
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $email;

    /**
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    protected $username;

    protected $salt;

    /**
     *
     * @ORM\Column(name="password", type="string", length=255)
     * @Serializer\Exclude()
     */
    protected $password;

    /**
     *
     * @var string
     */
    protected $plainPassword;

    /**
     *
     * @var array
     *
     * @ORM\Column(name="roles", type="json_array")
     */
    protected $roles = [];

    public function __construct()
    {
    }

    /**
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param mixed $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     *
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @param mixed $username
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @param mixed $password
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     *
     * @param
     *            $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        $this->password = null;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function isRequester()
    {
        return in_array('ROLE_REQUESTER', $this->roles);
    }

    public function isAdmin()
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function isDcInbound()
    {
        return in_array('ROLE_DC_INBOUND', $this->roles);
    }
}
