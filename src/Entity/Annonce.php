<?php

namespace App\Entity;

use App\Repository\AnnonceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
class Annonce
{

    const DEVISE = 'eur';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $hand_delivery = null;

    #[ORM\Column]
    private ?bool $shipement = null;

    #[ORM\Column]
    private ?bool $statut_livraison = null;

    #[ORM\Column]
    private ?float $price_origin = null;

    #[ORM\Column]
    private ?float $price_total = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column]
    private ?bool $plant_pot = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_expiration = null;

    #[ORM\Column]
    private ?bool $sold = false;

    #[ORM\Column(length: 255)]
    private ?string $poids = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acheteur = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\Column(length: 255)]
    private ?string $exp_adress = null;

    #[ORM\Column(length: 255)]
    private ?string $exp_zip_code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $exp_rel_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $exp_number = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $etiquette_url = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $tracing_url = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'annonce_id', targetEntity: Image::class, cascade: ['persist'])]
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    private ?Favori $favori = null;

    #[ORM\Column(nullable: true)]
    private ?bool $buyerDelivery = false;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isHandDelivery(): ?bool
    {
        return $this->hand_delivery;
    }

    public function setHandDelivery(bool $hand_delivery): self
    {
        $this->hand_delivery = $hand_delivery;

        return $this;
    }

    public function isShipement(): ?bool
    {
        return $this->shipement;
    }

    public function setShipement(bool $shipement): self
    {
        $this->shipement = $shipement;

        return $this;
    }

    public function isStatutLivraison(): ?bool
    {
        return $this->statut_livraison;
    }

    public function setStatutLivraison(bool $statut_livraison): self
    {
        $this->statut_livraison = $statut_livraison;

        return $this;
    }

    public function getPriceOrigin(): ?float
    {
        return $this->price_origin;
    }

    public function setPriceOrigin(float $price_origin): self
    {
        $this->price_origin = $price_origin;

        return $this;
    }

    public function getPriceTotal(): ?float
    {
        return $this->price_total;
    }

    public function setPriceTotal(float $price_total): self
    {
        $this->price_total = $price_total;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function isPlantPot(): ?bool
    {
        return $this->plant_pot;
    }

    public function setPlantPot(bool $plant_pot): self
    {
        $this->plant_pot = $plant_pot;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(\DateTimeInterface $date_expiration): self
    {
        $this->date_expiration = $date_expiration;

        return $this;
    }

    public function isSold(): ?bool
    {
        return $this->sold;
    }

    public function setSold(bool $sold): self
    {
        $this->sold = $sold;

        return $this;
    }

    public function getPoids(): ?string
    {
        return $this->poids;
    }

    public function setPoids(string $poids): self
    {
        $this->poids = $poids;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getAcheteur(): ?string
    {
        return $this->acheteur;
    }

    public function setAcheteur(?string $acheteur): self
    {
        $this->acheteur = $acheteur;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getExpAdress(): ?string
    {
        return $this->exp_adress;
    }

    public function setExpAdress(string $exp_adress): self
    {
        $this->exp_adress = $exp_adress;

        return $this;
    }

    public function getExpZipCode(): ?string
    {
        return $this->exp_zip_code;
    }

    public function setExpZipCode(string $exp_zip_code): self
    {
        $this->exp_zip_code = $exp_zip_code;

        return $this;
    }

    public function getExpRelId(): ?string
    {
        return $this->exp_rel_id;
    }

    public function setExpRelId(?string $exp_rel_id): self
    {
        $this->exp_rel_id = $exp_rel_id;

        return $this;
    }

    public function getExpNumber(): ?string
    {
        return $this->exp_number;
    }

    public function setExpNumber(?string $exp_number): self
    {
        $this->exp_number = $exp_number;

        return $this;
    }

    public function getEtiquetteUrl(): ?string
    {
        return $this->etiquette_url;
    }

    public function setEtiquetteUrl(?string $etiquette_url): self
    {
        $this->etiquette_url = $etiquette_url;

        return $this;
    }

    public function getTracingUrl(): ?string
    {
        return $this->tracing_url;
    }

    public function setTracingUrl(?string $tracing_url): self
    {
        $this->tracing_url = $tracing_url;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setAnnonceId($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getAnnonceId() === $this) {
                $image->setAnnonceId(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getFavori(): ?Favori
    {
        return $this->favori;
    }

    public function setFavori(?Favori $favori): self
    {
        $this->favori = $favori;

        return $this;
    }

    public function isBuyerDelivery(): ?bool
    {
        return $this->buyerDelivery;
    }

    public function setBuyerDelivery(?bool $buyerDelivery): self
    {
        $this->buyerDelivery = $buyerDelivery;

        return $this;
    }
}
