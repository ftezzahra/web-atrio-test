<?php

namespace App\Entity;

use App\Repository\EmploisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmploisRepository::class)
 */
class Emplois
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
    private ?string $nom_entreprise = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $poste = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_de_debut = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_de_fin = null;

    /**
     * @ORM\ManyToOne(targetEntity=Personnes::class, inversedBy="emplois")
     * @ORM\JoinColumn(nullable=false)
     */
    private $personne;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nom_entreprise;
    }

    public function setNomEntreprise(?string $nom_entreprise): static
    {
        $this->nom_entreprise = $nom_entreprise;

        return $this;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): static
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonne()
    {
        return $this->personne;
    }

    /**
     * @return null
     */
    public function getDateDeDebut()
    {
        return $this->date_de_debut;
    }

    /**
     * @param null $date_de_debut
     */
    public function setDateDeDebut($date_de_debut): void
    {
        $this->date_de_debut = $date_de_debut;
    }

    /**
     * @return null
     */
    public function getDateDeFin()
    {
        return $this->date_de_fin;
    }

    /**
     * @param null $date_de_fin
     */
    public function setDateDeFin($date_de_fin): void
    {
        $this->date_de_fin = $date_de_fin;
    }


    /**
     * @param mixed $personne
     */
    public function setPersonne($personne): void
    {
        $this->personne = $personne;
    }
}
