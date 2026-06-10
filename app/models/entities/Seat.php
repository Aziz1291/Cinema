<?php
class Seat
{
    private ?int $id = null;
    private ?int $room_id = null;
    private ?string $row_letter = null;
    private ?int $seat_number = null;

    public function __construct($room_id = null, $row_letter = null, $seat_number = null)
    {
        if ($room_id !== null) {
            $this->room_id = $room_id;
            $this->row_letter = $row_letter;
            $this->seat_number = $seat_number;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoomId()
    {
        return $this->room_id;
    }

    public function getRowLetter()
    {
        return $this->row_letter;
    }

    public function getSeatNumber()
    {
        return $this->seat_number;
    }
}
?>
