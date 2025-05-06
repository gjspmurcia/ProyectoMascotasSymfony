<?php

namespace App\Entity;

use App\Repository\QRRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

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

    #[Groups('mascota:read')]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups('mascota:read', 'mascota:write')]
    public function getImgQr(): ?string
    {
        return $this->imgQr;
    }

    #[Groups('mascota:read', 'mascota:write')]
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
