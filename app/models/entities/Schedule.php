<?php
class Schedule
{
    private ?int $id = null;
    private ?int $film_id = null;
    private ?int $room_id = null;
    private ?string $start_time = null;
    private ?string $end_time = null;
    private ?float $ticket_price = null;

    private ?string $film_title = null;
    private ?string $room_name = null;

    public function __construct($film_id = null, $room_id = null, $start_time = null, $end_time = null, $ticket_price = null)
    {
        if ($film_id !== null) {
            $this->film_id = $film_id;
            $this->room_id = $room_id;
            $this->start_time = $start_time;
            $this->end_time = $end_time;
            $this->ticket_price = $ticket_price;
        }
    }

    public function getId() { return $this->id; }
    public function getFilmId() { return $this->film_id; }
    public function getRoomId() { return $this->room_id; }
    public function getStartTime() { return $this->start_time; }
    public function getEndTime() { return $this->end_time; }
    public function getTicketPrice() { return $this->ticket_price; }

    public function getFilmTitle() { return $this->film_title; }
    public function getRoomName() { return $this->room_name; }
}
?>
