<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/entities/Schedule.php';

class ScheduleModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    private function baseQuery()
    {
        return 'SELECT s.*, f.title AS film_title, r.name AS room_name
                FROM schedules s
                JOIN films f ON s.film_id = f.id
                JOIN rooms  r ON s.room_id  = r.id';
    }

    public function hasOverlap($room_id, $start_time, $end_time, $exclude_id = 0)
    {
        $query = 'SELECT COUNT(*) FROM schedules 
                  WHERE room_id = ? 
                  AND ? < DATE_ADD(end_time, INTERVAL 15 MINUTE) 
                  AND start_time < DATE_ADD(?, INTERVAL 15 MINUTE)
                  AND id != ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$room_id, $start_time, $end_time, $exclude_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function getAllSchedules()
    {
        $stmt = $this->db->prepare($this->baseQuery() . ' WHERE s.end_time > NOW() ORDER BY s.start_time ASC');
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Schedule');
        return $stmt->fetchAll() ?: [];
    }

    public function getSchedulesByFilmId($film_id)
    {
        $stmt = $this->db->prepare($this->baseQuery() . ' WHERE s.film_id = ? AND s.end_time > NOW() ORDER BY s.start_time ASC');
        $stmt->execute([$film_id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Schedule');
        return $stmt->fetchAll() ?: [];
    }

    public function getScheduleById($id)
    {
        $stmt = $this->db->prepare($this->baseQuery() . ' WHERE s.id = ?');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Schedule');
        return $stmt->fetch() ?: null;
    }

    public function createSchedule($film_id, $room_id, $start_time, $end_time, $ticket_price)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO schedules (film_id, room_id, start_time, end_time, ticket_price) VALUES (?, ?, ?, ?, ?)'
        );
        return $stmt->execute([$film_id, $room_id, $start_time, $end_time, $ticket_price]);
    }

    public function updateSchedule($id, $film_id, $room_id, $start_time, $end_time, $ticket_price)
    {
        $stmt = $this->db->prepare(
            'UPDATE schedules SET film_id=?, room_id=?, start_time=?, end_time=?, ticket_price=? WHERE id=?'
        );
        return $stmt->execute([$film_id, $room_id, $start_time, $end_time, $ticket_price, $id]);
    }

    public function deleteSchedule($id)
    {
        $stmt = $this->db->prepare('DELETE FROM schedules WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
