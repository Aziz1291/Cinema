<?php
class Reservation
{
    private ?int $id = null;
    private ?int $user_id = null;
    private ?int $seat_id = null;
    private ?int $schedule_id = null;

    private ?string $film_title = null;
    private ?string $room_name = null;
    private ?string $start_time = null;
    private ?string $row_letter = null;
    private ?int $seat_number = null;

    private ?string $customer_name = null;
    private ?string $user_name = null;
    private ?string $user_email = null;
    private ?string $poster_image = null;
    private ?float $ticket_price = null;

    public function __construct($user_id = null, $seat_id = null, $schedule_id = null)
    {
        if ($user_id !== null) {
            $this->user_id = $user_id;
            $this->seat_id = $seat_id;
            $this->schedule_id = $schedule_id;
        }
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getSeatId() { return $this->seat_id; }
    public function getScheduleId() { return $this->schedule_id; }

    public function getFilmTitle() { return $this->film_title; }
    public function getRoomName() { return $this->room_name; }
    public function getStartTime() { return $this->start_time; }
    public function getRowLetter() { return $this->row_letter; }
    public function getSeatNumber() { return $this->seat_number; }

    public function getCustomerName() { return $this->customer_name; }
    public function getUserName() { return $this->user_name; }
    public function getUserEmail() { return $this->user_email; }
    public function getPosterImage() { return $this->poster_image; }
    public function getTicketPrice() { return $this->ticket_price; }
}
