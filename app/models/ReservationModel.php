<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/entities/Reservation.php';

class ReservationModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getReservationsByUserId($user_id)
    {
        $query = '
            SELECT res.*, f.title as film_title, r.name as room_name, s.start_time, st.row_letter, st.seat_number
            FROM reservations res
            JOIN schedules s ON res.schedule_id = s.id
            JOIN films f ON s.film_id = f.id
            JOIN rooms r ON s.room_id = r.id
            JOIN seats st ON res.seat_id = st.id
            WHERE res.user_id = ?
            ORDER BY s.start_time DESC
        ';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Reservation');
        return $stmt->fetchAll() ?: [];
    }

    public function getAllReservations($search = '')
    {
        $query = '
            SELECT res.*, f.title as film_title, f.poster_image, r.name as room_name, s.start_time, s.ticket_price, st.row_letter, st.seat_number,
                   CONCAT(u.first_name, \' \', u.last_name) as customer_name, u.username as user_name, u.email as user_email
            FROM reservations res
            JOIN schedules s ON res.schedule_id = s.id
            JOIN films f ON s.film_id = f.id
            JOIN rooms r ON s.room_id = r.id
            JOIN seats st ON res.seat_id = st.id
            JOIN users u ON res.user_id = u.id
        ';
        $params = [];
        if (!empty($search)) {
            $query .= ' WHERE (f.title LIKE ? OR u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR r.name LIKE ?) ';
            $term = '%' . $search . '%';
            $params = [$term, $term, $term, $term, $term, $term];
        }
        $query .= ' ORDER BY s.start_time DESC, res.id DESC ';
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Reservation');
        return $stmt->fetchAll() ?: [];
    }

    public function getReservedSeatIdsByScheduleId($schedule_id)
    {
        $query = 'SELECT seat_id FROM reservations WHERE schedule_id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$schedule_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    // All-or-nothing: if one seat is already taken, none of the seats is booked.
    public function createReservations($user_id, array $seat_ids, $schedule_id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare('INSERT INTO reservations (user_id, seat_id, schedule_id) VALUES (?, ?, ?)');
            foreach ($seat_ids as $seat_id) {
                $stmt->execute([$user_id, $seat_id, $schedule_id]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function createReservation($user_id, $seat_id, $schedule_id)
    {
        try {
            $query = 'INSERT INTO reservations (user_id, seat_id, schedule_id) VALUES (?, ?, ?)';
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$user_id, $seat_id, $schedule_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
