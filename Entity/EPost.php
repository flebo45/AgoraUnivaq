<?php
require_once('vendor/autoload.php');
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * @ORM\Entity
 * @ORM\Table(name="post")
 */

class EPost{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $idPost;

     /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     */
    private $category;

    /** @ORM\Column(type="datetime") */
    private DateTime $creation_time; 

    /**
    * @ORM\Column(type="boolean")
    */
    private $removed = false;

    /**
     * @ORM\ManyToOne(targetEntity="EUser")
     * @ORM\JoinColumn(name="idUser", referencedColumnName="idUser")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="EImage", mappedBy="post", cascade={"remove"})
     */
    private $images;


    private static $entity = EPost::class;

    public function __construct($title, $description, $category)
    {
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->setTime();
        $this->images = [];
        
    }
    
    private function setTime(){
        $this->creation_time = new DateTime("now");
    }

    public function getTime()
    {
        return $this->creation_time;
    }

    public function setCreationTime($dateTime){
        $this->creation_time = $dateTime;
    }

    public function getTimeStr()
    {
        return $this->creation_time->format('Y-m-d H:i:s');
    }

    public static function getEntity(): string
    {
        return self::$entity;
    }

    public function getId()
    {
        return $this->idPost;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(EUser $user): void
    {
        $this->user = $user;
    }

    public function getTitle()
    {
        return $this->title;
    }

        public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category): void
    {
        $this->category = $category;
    }

    public function isBanned(): bool
    {
        return $this->removed;
    }

    public function setBan(bool $removed): void
    {
        $this->removed = $removed;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function addImage(EImage $image): void
    {
        $imageId = $image->getId(); // Assuming you have a method to get the image ID
    
        // Check if the image with the same ID exists in the images array
        $imageExists = false;
        foreach ($this->images as $existingImage) {
            if ($existingImage->getId() === $imageId) {
                $imageExists = true;
                break;
            }
        }

        // If the image doesn't exist in the array, add it
        if (!$imageExists) {
            $this->images[] = $image;
            $image->setPost($this);
        }
    }

    public function setId($id){
        $this->idPost = $id;
    }
}