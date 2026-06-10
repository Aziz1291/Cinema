<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/entities/Film.php';

class FilmModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAllFilms($search = '')
    {
        $query = 'SELECT f.*, COUNT(s.id) AS active_sessions_count 
                  FROM films f 
                  LEFT JOIN schedules s ON f.id = s.film_id AND s.end_time > NOW()';
        $params = [];
        if ($search !== '') {
            $query .= ' WHERE f.title LIKE ?';
            $params[] = '%' . $search . '%';
        }
        $query .= ' GROUP BY f.id ORDER BY f.created_at DESC, f.id DESC';
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Film');
        return $stmt->fetchAll() ?: [];
    }

    public function getFilmById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM films WHERE id = ?');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Film');
        return $stmt->fetch() ?: null;
    }

    public function createFilm($title, $duration, $description, $poster_image)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO films (title, duration, description, created_at, poster_image) VALUES (?, ?, ?, CURDATE(), ?)'
        );
        return $stmt->execute([$title, $duration, $description, $poster_image]);
    }

    public function updateFilm($id, $title, $duration, $description, $poster_image)
    {
        $stmt = $this->db->prepare(
            'UPDATE films SET title=?, duration=?, description=?, poster_image=? WHERE id=?'
        );
        return $stmt->execute([$title, $duration, $description, $poster_image, $id]);
    }

    public function deleteFilm($id)
    {
        $stmt = $this->db->prepare('DELETE FROM films WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
