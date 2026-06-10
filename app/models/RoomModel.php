<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/entities/Room.php';

class RoomModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAllRooms()
    {
        $stmt = $this->db->prepare('SELECT * FROM rooms ORDER BY id ASC');
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Room');
        return $stmt->fetchAll() ?: [];
    }

    public function getRoomById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Room');
        return $stmt->fetch() ?: null;
    }

    public function createRoom($name, $rowsNumber, $seatsNumber)
    {
        $stmt = $this->db->prepare('INSERT INTO rooms (name, rowsNumber, seatsNumber) VALUES (?, ?, ?)');
        $ok = $stmt->execute([$name, $rowsNumber, $seatsNumber]);
        if ($ok) {
            $this->generateSeats($this->db->lastInsertId(), $rowsNumber, $seatsNumber);
        }
        return $ok;
    }

    private function generateSeats($roomId, $rowsNumber, $seatsNumber)
    {
        $seatsPerRow = ceil($seatsNumber / $rowsNumber);
        $created = 0;
        $stmt = $this->db->prepare('INSERT INTO seats (room_id, row_letter, seat_number) VALUES (?, ?, ?)');
        
        for ($r = 0; $r < $rowsNumber; $r++) {
            $rowLetter = chr(65 + $r);
            for ($s = 1; $s <= $seatsPerRow; $s++) {
                if ($created >= $seatsNumber) break;
                $stmt->execute([$roomId, $rowLetter, $s]);
                $created++;
            }
        }
    }

    public function updateRoom($id, $name, $rowsNumber, $seatsNumber)
    {
        $stmt = $this->db->prepare('UPDATE rooms SET name=?, rowsNumber=?, seatsNumber=? WHERE id=?');
        return $stmt->execute([$name, $rowsNumber, $seatsNumber, $id]);
    }

    public function deleteRoom($id)
    {
        $stmt = $this->db->prepare('DELETE FROM rooms WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
