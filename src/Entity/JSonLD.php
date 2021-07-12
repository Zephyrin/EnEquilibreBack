<?php

namespace App\Entity;

use App\Repository\JSonLDRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;


/**
 * @ORM\Entity(repositoryClass=JSonLDRepository::class)
 */
class JSonLD
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=150, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @SerializedName("json")
     */
    private $JSon;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getJSon(): ?string
    {
        return $this->JSon;
    }

    public function setJSon(string $JSon): self
    {
        $this->JSon = $JSon;

        return $this;
    }
}
