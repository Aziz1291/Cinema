<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/entities/Seat.php';

class SeatModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getSeatsByRoomId($room_id)
    {
        $query = 'select * from seats where room_id = ? order by row_letter asc, seat_number asc';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$room_id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Seat');
        $seats = $stmt->fetchAll();
        return $seats ?: [];
    }

    public function getSeatById($id)
    {
        $query = 'select * from seats where id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Seat');
        $seat = $stmt->fetch();
        return $seat ?: null;
    }
}
?>
