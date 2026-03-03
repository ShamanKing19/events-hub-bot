<?php

namespace App\Entity;

use App\Event\EventRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'events')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 512)]
    private ?string $name = null;

    /**
     * @var Collection<int, StudentEvent>
     */
    #[ORM\OneToMany(targetEntity: StudentEvent::class, mappedBy: 'event')]
    private Collection $studentEvents;

    #[ORM\Column(nullable: false)]
    private ?DateTime $startDate = null;

    #[ORM\Column(nullable: false)]
    private ?DateTime $finishDate = null;

    #[ORM\Column(nullable: true, columnDefinition: 'DATETIME DEFAULT CURRENT_TIMESTAMP')]
    private ?DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true, columnDefinition: 'DATETIME DEFAULT CURRENT_TIMESTAMP')]
    private ?DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->studentEvents = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, StudentEvent>
     */
    public function getStudentEvents(): Collection
    {
        return $this->studentEvents;
    }

    public function addStudentEvent(StudentEvent $studentEvent): static
    {
        if (!$this->studentEvents->contains($studentEvent)) {
            $this->studentEvents->add($studentEvent);
            $studentEvent->setEvent($this);
        }

        return $this;
    }

    public function removeStudentEvent(StudentEvent $studentEvent): static
    {
        if ($this->studentEvents->removeElement($studentEvent)) {
            if ($studentEvent->getEvent() === $this) {
                $studentEvent->setEvent(null);
            }
        }

        return $this;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getFinishDate(): ?DateTime
    {
        return $this->finishDate;
    }

    public function setFinishDate(DateTime $finishDate): static
    {
        $this->finishDate = $finishDate;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
