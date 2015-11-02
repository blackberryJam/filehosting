<?php
namespace Filehosting\Model;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="comments", indexes={@ORM\Index(name="path_idx", columns={"path"}),
 *                                      @ORM\Index(name="parentPath_idx", columns={"parentPath"})})
 */
class Comment implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="comments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="File", inversedBy="comments")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="CASCADE", nullable=FALSE)
     */
    protected $file;

    /**
     * @ORM\Column
     */
    protected $body;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="string", length=39, nullable=FALSE)
     */
    protected $path;

    /**
     * @ORM\Column(type="string", length=35, nullable=TRUE)
     */
    protected $parentPath = null;

    /**
     * @ORM\Column(type="integer", nullable=FALSE)
     */
    protected $number;

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $user->addComment($this);
        $this->user = $user;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(File $file)
    {
        $file->addComment($this);
        $this->file = $file;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
        $this->depth = $this->getDepth();

        $arr = explode("-", $path);
        $this->number = end($arr);

        if ($this->depth !== 1) {
            $this->parentPath = substr_replace($this->path, "", -4);
        } else {
            $this->parentPath = null;
        }
    }

    public function getParentPath()
    {
        return $this->parentPath;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getDepth()
    {
        return count(explode("-", $this->path));
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'userName' => $this->user->getName(),
            'body' => $this->body,
            'date' => $this->date->format("Y-m-d, H:i:s"),
            'path' => $this->path,
            'parentPath' => $this->parentPath ? $this->parentPath : "",
            'number' => (int) $this->number,
            'depth' => $this->getDepth()
        );
    }
}
