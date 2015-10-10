<?php
namespace Filehosting\Model;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=140, nullable=TRUE)
     */
    protected $name;

    /**
     * @ORM\Column(name="hashed_password", type="string", nullable=TRUE)
     */
    protected $hashedPassword;

    /**
     * @ORM\Column(type="string", unique=TRUE, length=140, nullable=TRUE)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=32, unique=TRUE, name="cookie_token", nullable=TRUE)
     */
    protected $cookieToken;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="user")
     */
    protected $files;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="user")
     */
    protected $comments;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getHashedPassword()
    {
        return $this->hashedPassword;
    }

    public function setHashedPassword($hashedPassword)
    {
        $this->hashedPassword = $hashedPassword;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getCookieToken()
    {
        return $this->cookieToken;
    }

    public function setCookieToken($cookieToken)
    {
        $this->cookieToken = $cookieToken;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addFile(File $file)
    {
        $this->files[] = $file;
    }

    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;
    }

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }
}
