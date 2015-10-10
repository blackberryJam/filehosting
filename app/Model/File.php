<?php
namespace Filehosting\Model;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="files")
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="files")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="file")
     */
    protected $comments;

    /**
     * @ORM\Column(name="path", type="string", length=1000)
     */
    protected $path;

    /**
     * @ORM\Column(name="original_name", type="string", length=140)
     */
    protected $originalName;

    /**
     * @ORM\Column(type="string", name="mime_type", length=140, nullable=TRUE)
     */
    protected $mimeType;

    /**
     * @ORM\Column(name="date_upload", type="datetime")
     */
    protected $dateUpload;

    /**
     * @ORM\Column(type="string", length=5000, name="media_info", nullable=TRUE)
     */
    protected $mediaInfo;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $size;

    /**
     * @ORM\Column(type="string", length=1000, name="thumbnail_path", nullable=TRUE)
     */
    protected $thumbnailPath;

    public function getId()
    {
        return $this->id;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getOriginalName()
    {
        return $this->originalName;
    }

    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
    }

    public function getDirectory()
    {
        $pathInfo = pathinfo($this->fullPath);
        return $pathInfo['dirname'];
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getDateUpload()
    {
        return $this->dateUpload;
    }

    public function setDateUpload(\DateTime $dateUpload)
    {
        $this->dateUpload = $dateUpload;
    }

    public function getExtension()
    {
        $extension = "";

        $pathInfo = pathinfo($this->fullPath);
        if (isset($pathInfo['extension'])) {
            $extension = $pathInfo['extension'];
        }

        return $extension;
    }

    public function getMediaInfo()
    {
        return $this->mediaInfo;
    }

    public function setMediaInfo($mediaInfo)
    {
        $this->mediaInfo = $mediaInfo;
    }

    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;
    }

    public function setUser(User $user)
    {
        $user->addFile($this);
        $this->user = $user;
    }

    public function getBaseName()
    {
        return basename($this->path);
    }

    public function getThumbnailPath()
    {
        return $this->thumbnailPath;
    }

    public function setThumbnailPath($thumbnailPath)
    {
        $this->thumbnailPath = $thumbnailPath;
    }

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }
}
