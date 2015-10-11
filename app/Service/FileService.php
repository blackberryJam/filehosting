<?php
namespace Filehosting\Service;
use Filehosting\Model\File;
use Filehosting\Model\User;

class FileService
{
    protected $getid3;
    protected $em;
    protected $user;

    protected $managedFile;

    protected $saveDirectory;
    protected $destinationFolder;

    protected $thumbnailPath = null;

    protected $mediaInfoFields = array('playtime_string', 'audio', 'video');
    protected $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');
    protected $maxThumnailResolution = '300x300';

    public function __construct($getid3, \Doctrine\ORM\EntityManager $entityManager, $saveDirectory)
    {
        $this->getid3 = $getid3;
        $this->em = $entityManager;
        $this->saveDirectory = $saveDirectory;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function manageUploadedFile(\SplFileInfo $uploadedFile)
    {
        $file = $this->saveToStorageAndGetFileEntity($uploadedFile);
        $this->em->persist($file);
        $this->managedFile = $file;
    }

    public function getManagedFile()
    {
        return $this->managedFile;
    }

    public function getManagedFileId()
    {
        return $this->managedFile->getId();
    }

    public function getFileByIdOrCreateNewIfNotExists($id)
    {
        $file = $this->em->find('\Filehosting\Model\File', $id);
        if ($file) {
            return $file;
        }
        return new File();
    }

    public function removeFile(File $file)
    {
        $this->removeFileFromDataBase($file);
        if(!$this->hasOwner($file)) {
            $this->removeFileFromStorage($file);
        }
    }

    protected function removeFileFromDataBase(File $file) {
        $this->em->remove($file);
    }

    protected function removeFileFromStorage(File $file) {
        unlink("{$this->saveDirectory}/{$file->getPath()}");
    }

    protected function hasOwner(File $file)
    {
        $fileRepo = $this->em->getRepository('\Filehosting\Model\File');
        $result = $fileRepo->findBy(array('path' => $file->getPath()));
        if (sizeof($result) > 1) {
            return true;
        }
        return false;
    }

    protected function extractMediaInfo(\SplFileInfo $uploadedFile)
    {
        $tmpPath = $uploadedFile->getRealPath();

        $mediaInfo = $this->getid3->analyze($tmpPath);
        $mediaInfo = $this->filterMediaInfo($mediaInfo);

        if ((bool) $mediaInfo) {
            $mediaInfo = $this->convertToString($mediaInfo);
            return $mediaInfo;
        }
    }

    protected function filterMediaInfo($mediaInfo)
    {
        $permitted = $this->mediaInfoFields;
        $filteredInfo = array_filter($mediaInfo, function($infoName) use ($permitted) {
            return in_array($infoName, $permitted);
        }, ARRAY_FILTER_USE_KEY);
        unset($filteredInfo['audio']['streams']);
        unset($filteredInfo['video']['streams']);
        return $filteredInfo;
    }

    protected function convertToString($array)
    {
        return http_build_query($array);
    }

    public function getArrayOfMediaInfo(File $file)
    {
        return $this->convertMediaInfoToArray($file->getMediaInfo());
    }

    protected function convertMediaInfoToArray($mediaInfo)
    {
        if (isset($mediaInfo)) {
            parse_str($mediaInfo, $mediaInfo);
            if (!isset($mediaInfo['playtime_string'])) {
                $mediaInfo['playtime_string'] = "";
            }
            return $mediaInfo;
        }
        return array();
    }

    protected function saveToStorageAndGetFileEntity(\SplFileInfo $uploadedFile)
    {
        $hash = md5_file($uploadedFile->getRealPath());

        $folder1 = mb_substr($hash, 0, 2);
        $folder2 = mb_substr($hash, 2, 2);
        $folder3 = mb_substr($hash, 4, 2);
        $this->destinationFolder = "{$folder1}/{$folder2}/{$folder3}";
        $fileName = mb_substr($hash, 6) . ".{$uploadedFile->getClientOriginalExtension()}";

        $file = $this->convertSavedFileToEntity($uploadedFile, $fileName);

        $dir = "{$this->saveDirectory}/{$this->destinationFolder}";
        if (!file_exists("{$dir}/{$fileName}")) {
            mkdir($dir, 0777, true);
            $uploadedFile->move($dir, $fileName);
        }

        return $file;
    }

    protected function convertSavedFileToEntity($uploadedFile, $newName)
    {
        $file = new File();

        $file->setPath("{$this->destinationFolder}/{$newName}");
        $file->setOriginalName($uploadedFile->getClientOriginalName());
        $file->setMimeType($uploadedFile->getMimeType());
        $file->setDateUpload(new \DateTime());
        $file->setMediaInfo($this->extractMediaInfo($uploadedFile));
        $file->setThumbnailPath($this->thumbnailPath);
        $file->setUser($this->user);
        $size = round($uploadedFile->getSize() / (1024 * 1024), 2);
        $file->setSize("{$size} MB");

        return $file;

    }
}
