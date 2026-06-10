<?php
class Room
{
    private ?int $id = null;
    private ?string $name = null;
    private ?int $rowsNumber = null;
    private ?int $seatsNumber = null;

    public function __construct($name = null, $rowsNumber = null, $seatsNumber = null)
    {
        if ($name !== null) {
            $this->name = $name;
            $this->rowsNumber = $rowsNumber;
            $this->seatsNumber = $seatsNumber;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRowsNumber()
    {
        return $this->rowsNumber;
    }

    public function getSeatsNumber()
    {
        return $this->seatsNumber;
    }
}
?>
