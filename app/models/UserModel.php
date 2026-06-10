<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__.'/entities/User.php';
class UserModel
{
    private $db;
    public function __construct()
    {
        $database=new Database();
        $this->db=$database->connect();
    }
    public function getUserById($id)
    {
        $query='select * from users where id=?';
        $stmt=$this->db->prepare($query);
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS,'User');
        $user=$stmt->fetch();
        return $user;
    }
    public function getAllUsers()
    {
        $query='select * from users';
        $stmt=$this->db->query($query);
        $stmt->setFetchMode(PDO::FETCH_CLASS,'User');
        return $stmt->fetchAll() ?: [];
    }
    public function getUserByUsernameOrEmail($usernameORemail)
    {
        $query='select * from users where username=? or email=?';
        $stmt=$this->db->prepare($query);
        $stmt->execute([$usernameORemail,$usernameORemail]);
        $stmt->setFetchMode(PDO::FETCH_CLASS,'User');
        $user=$stmt->fetch();
        return $user;
    }
    public function addUser($username,$email,$first_name,$last_name,$password,$birth_date)
    {
        
        $query='insert into users(username,email,first_name,last_name,password,birth_date) values(?,?,?,?,?,?)';
        $stmt=$this->db->prepare($query);
        return $stmt->execute([$username,$email,$first_name,$last_name,$password,$birth_date]);
    }
    public function uniqueUsername($username)
    {
        $query='select count(*) from users where username=?';
        $stmt=$this->db->prepare($query);
        $stmt->execute([$username]);
        return $stmt->fetchColumn()==0;
    }
    public function uniqueEmail($email)
    {
        $query='select count(*) from users where email=?';
        $stmt=$this->db->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetchColumn()==0;
        

    }

    public function isAdmin($userId)
    {
        $user = $this->getUserById($userId);
        return $user && $user->getRole() === 'admin';
    }
}