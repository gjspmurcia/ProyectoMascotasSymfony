<?php

namespace App\Entity;

use App\Repository\MascotaRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MascotaRepository::class)]
class Mascota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('mascota:read')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('mascota:read', 'mascota:write')]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('mascota:read', 'mascota:write')]
    private ?string $num_chip = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('mascota:read', 'mascota:write')]
    private ?string $observaciones = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('mascota:read', 'mascota:write')]
    private ?string $foto = null;

    #[ORM\ManyToOne(inversedBy: 'mascotas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('mascota:read', 'mascota:write')]
    private ?Usuario $id_usuario = null;

    #[ORM\OneToOne(mappedBy: 'id_mascota', cascade: ['persist', 'remove'])]
    #[Groups('mascota:read', 'mascota:write')]
    private ?QR $qr = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getNumChip(): ?string
    {
        return $this->num_chip;
    }

    public function setNumChip(?string $num_chip): static
    {
        $this->num_chip = $num_chip;

        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): static
    {
        $this->foto = $foto;

        return $this;
    }

    public function getIdUsuario(): ?Usuario
    {
        return $this->id_usuario;
    }

    public function setIdUsuario(?Usuario $id_usuario): static
    {
        $this->id_usuario = $id_usuario;

        return $this;
    }

    public function getQr(): ?QR
    {
        return $this->qr;
    }

    public function setQr(QR $qr): static
    {
        // set the owning side of the relation if necessary
        if ($qr->getIdMascota() !== $this) {
            $qr->setIdMascota($this);
        }

        $this->qr = $qr;

        return $this;
    }
}
