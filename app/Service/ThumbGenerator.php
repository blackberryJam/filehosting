<?php
namespace Filehosting\Service;

class ThumbGenerator
{
    const MODE_CROP = 0;
    const MODE_SCALE = 1;

    protected $thumbDirectory;
    protected $sourceDirectory;

    protected $sourceImageFile;
    protected $createdThumb;
    protected $imageExtension;

    protected $maxThumbWidth = 300;
    protected $maxThumbHeight = 300;

    public function __construct($thumbDirectory)
    {
        $this->thumbDirectory = $thumbDirectory;
    }

    public function setSourceImageFile(\Filehosting\Model\File $file)
    {
        $this->sourceImageFile = $file;
        $this->imageExtension = $file->getExtension();
    }

    public function setDirectory($directory)
    {
        $this->sourceDirectory = $directory;
        $this->thumbDirectory = "{$directory}/thumbs";
    }

    public function getManagedFile()
    {
        return $this->sourceImageFile;
    }

    public function setMaxThumbResolution($x, $y)
    {
        $this->maxThumbWidth = $x;
        $this->maxThumbHeight = $y;
    }

    public function generateThumb($mode)
    {
        switch ($mode) {
            case self::MODE_CROP:
                $thumbPath = $this->cropImage();
                break;
            case self::MODE_SCALE:
                $thumbPath = $this->scaleImage();
                break;
        }

        $this->sourceImageFile->setThumbnailPath($thumbPath);
    }

    protected function cropImage()
    {
        $image_src = $this->imageCreateFromAny("{$this->sourceDirectory}/{$this->sourceImageFile->getPath()}");

        $width_src = imagesx($image_src);
        $height_src = imagesy($image_src);

        $width_thumb = min($this->maxThumbWidth, $width_src);
        $height_thumb = min($this->maxThumbHeight, $height_src);

        $thumb = imagecreatetruecolor($width_thumb, $height_thumb);

        $ratio_src = $width_src / $height_src;
        $ratio_thumb = $width_thumb / $height_thumb;

        if ($ratio_src > $ratio_thumb) {
            $scale_width = $width_src / ($height_src / $height_thumb);
            $scale_height = $height_thumb;
        } else {
            $scale_height = $height_src / ($width_src / $width_thumb);
            $scale_width = $width_thumb;
        }

        imagecopyresampled($thumb,
                           $image_src,
                           0 - ($scale_width - $width_thumb) / 2,
                           0 - ($scale_height - $height_thumb) / 2,
                           0, 0,
                           $scale_width, $scale_height,
                           $width_src, $height_src);

        $thumbPath = "{$this->sourceImageFile->getDirectory()}/thumb_{$this->sourceImageFile->getBaseName()}";
        $this->imageAny($thumb, "{$this->thumbDirectory}/{$thumbPath}");

        return "thumbs/{$thumbPath}";
    }

    protected function scaleImage()
    {
        $image_src = $this->imageCreateFromAny("{$this->sourceDirectory}/{$this->sourceImageFile->getPath()}");

        $width_src = imagesx($image_src);
        $height_src = imagesy($image_src);

        $width_thumb = min($this->maxThumbWidth, $width_src);
        $height_thumb = min($this->maxThumbHeight, $height_src);

        $ratio_src = $width_src / $height_src;
        $ratio_thumb = $width_thumb / $height_thumb;

        if ($ratio_src > $ratio_thumb) {
            $height_thumb = $height_src / ($width_src / $width_thumb);
        } else {
            $width_thumb = $width_src / ($height_src / $height_thumb);
        }

        $thumb = imagecreatetruecolor($width_thumb, $height_thumb);

        imagecopyresampled($thumb,
                           $image_src,
                           0, 0,
                           0, 0,
                           $width_thumb, $height_thumb,
                           $width_src, $height_src);

        $thumbPath = "{$this->sourceImageFile->getDirectory()}/thumb_{$this->sourceImageFile->getBaseName()}";
        $this->imageAny($thumb, "{$this->thumbDirectory}/{$thumbPath}");

        return "thumbs/{$thumbPath}";
    }

    protected function imageCreateFromAny($path)
    {
        $extension = $this->getExtension();

        $imageCreate = "imagecreatefrom{$extension}";

        return $imageCreate($path);
    }

    protected function imageAny($image, $path)
    {
        if (!file_exists($path)) {
            mkdir(dirname($path), 0777, true);
        }

        $extension = $this->getExtension();

        $imageAny = "image{$extension}";

        return $imageAny($image, $path);
    }

    protected function getExtension()
    {
        $extension = $this->sourceImageFile->getExtension();
        $extension = $extension === "jpg" ? "jpeg" : $extension;

        return $extension;
    }
}
