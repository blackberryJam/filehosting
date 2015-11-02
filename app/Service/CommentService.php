<?php
namespace Filehosting\Service;
use Filehosting\Model\Comment;
use Filehosting\Model\User;
use Filehosting\Model\FIle;

class CommentService
{
    const VALIDATION_FAILED = 0;

    protected $em;
    protected $user;
    protected $file;

    protected $validationErrors = array();

    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setFile(File $file)
    {
        $this->file = $file;
    }

    public function manageFormData($formData)
    {
        $this->validateFormData($formData);

        if (!empty($this->validationErrors)) {
            return self::VALIDATION_FAILED;
        }

        $comment = $this->createCommentEntity($formData);
        $this->em->persist($comment);

        return $comment;
    }

    protected function validateFormData($formData)
    {
        $this->validateCommentBody($formData['body']);
        $this->validateCommentParentPath($formData['parent']);
    }

    protected function validateCommentBody($commentBody)
    {
        //TODO!!!
    }

    protected function validateCommentParentPath($parentPath)
    {
        //TODO!!!
    }

    protected function createCommentEntity($formData)
    {
        $comment = new Comment();

        $comment->setDate(new \DateTime());
        $comment->setBody($formData['body']);
        $comment->setUser($this->user);
        $comment->setFile($this->file);
        $comment->setPath($this->createCommentPath($formData['parent']));

        return $comment;
    }

    protected function createCommentPath($parentPath)
    {
        $number = $this->getActualCommentNumber($parentPath);
        return $parentPath === "" ? "{$number}" : "{$parentPath}-{$number}";
    }

    protected function getActualCommentNumber($parentPath)
    {
        $lastNumber = $this->getLastCommentNumber($parentPath);
        return $this->incrementNumberAndGetStringValue($lastNumber);
    }

    protected function getLastCommentNumber($parentPath)
    {
        $parentPath = $parentPath === "" ? null : $parentPath;
        $childs = $this->file->getComments()->filter(function($comment) use ($parentPath) {
            return $comment->getParentPath() === $parentPath;
        });

        if ($childs->count() === 0) {
            return 0;
        }

        $max = 0;
        foreach ($childs as $child) {
            if ($child->getNumber() > $max) {
                $max = $child->getNumber();
            }
        }

        return $max;
    }

    protected function incrementNumberAndGetStringValue($number)
    {
        $number += 1;
        $string = (string) $number;
        for ($i = mb_strlen($string); $i < 3; $i++) {
            $string = "0{$string}";
        }
        return $string;
    }

    /*protected function getLastChildNumber($parentPath)
    {
        $query = $this->em->createQuery('SELECT MAX(c.number) result
                    FROM Filehosting\Model\Comment c WHERE c.parentPath = :path AND IDENTITY(c.file) = :id');
        $query->setParameter('path', $parentPath);
        $query->setParameter('id', $this->file->getId());
        return $query->getSingleScalarResult();
        //return $query->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);
    }

    protected function getLastRootCommentNumber()
    {
        $query = $this->em->createQuery('SELECT MAX(c.number) result
                    FROM Filehosting\Model\Comment c WHERE c.parentPath IS NULL AND IDENTITY(c.file) = :id');
        $query->setParameter('id', $this->file->getId());
        return $query->getSingleScalarResult();
        //return $query->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);
    }*/
}
