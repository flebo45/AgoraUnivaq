<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="likes")
 */

class ELike{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $idLike;

    /**
     * @ORM\Column(type="integer")
     */
    private $idUser;

    /**
     * @ORM\Column(type="integer")
     */
    private $idPost;

    private static $entity = ELike::class;


    public function __construct($idUser, $idPost)
    {
        $this->idUser = $idUser;
        $this->idPost = $idPost;
    }

    public static function getEntity(): string
    {
        return self::$entity;
    }

    public function getId()
    {
        return $this->idLike;
    }

    public function getIdUser()
    {
        return $this->idUser;
    }

    public function getIdPost()
    {
        return $this->idPost;
    }

    public function setId($id)
    {
        $this->idLike = $id;
    }
}