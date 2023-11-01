<?php

namespace App\Entity;

use App\Repository\PersonnesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * @ORM\Entity(repositoryClass=PersonnesRepository::class)
 */
class Personnes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_de_naissance = null;

    /**
     * @ORM\OneToMany(targetEntity=Emplois::class, mappedBy="personnes")
     */
    private $emplois;

    public function __construct()
    {
        $this->emplois = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return null
     */
    public function getDateDeNaissance()
    {
        return $this->date_de_naissance;
    }

    /**
     * @param null $date_de_naissance
     */
    public function setDateDeNaissance($date_de_naissance): void
    {
        $this->date_de_naissance = $date_de_naissance;
    }

    /**
     * @return Collection|Emploi[]
     */
    public function getEmplois(): Collection {
        return $this->emplois;
    }

    /**
     * @param ArrayCollection $emplois
     */
    public function setEmplois(ArrayCollection $emplois): void
    {
        $this->emplois = $emplois;
    }




}
