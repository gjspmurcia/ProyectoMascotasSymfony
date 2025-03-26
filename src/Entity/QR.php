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
    private ?string $img_qr = null;

    #[ORM\OneToOne(inversedBy: 'qr', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mascota $id_mascota = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImgQr(): ?string
    {
        return $this->img_qr;
    }

    public function setImgQr(string $img_qr): static
    {
        $this->img_qr = $img_qr;

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
