<?php

namespace App\Entity;

use App\Repository\QRRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QRRepository::class)]
class QR
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['mascota:read', 'qr:read'])]
    private ?string $imgQr = null;

    #[ORM\OneToOne(inversedBy: 'qr', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mascota $id_mascota = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImgQr(): ?string
    {
        return $this->imgQr;
    }

    public function setImgQr(string $imgQr): static
    {
        $this->imgQr = $imgQr;

        return $this;
    }

    public function getIdMascota(): ?Mascota
    {
        return $this->id_mascota;
    }

    public function setIdMascota(Mascota $id_mascota): static
    {
        $this->id_mascota = $id_mascota;

        return $this;
    }
}
