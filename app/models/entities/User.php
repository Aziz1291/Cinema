<?php 
class User
{
    private ?int $id;
    private ?string $username;
    private ?string $first_name;
    private ?string $last_name;
    private ?string $email;
    private ?string $password;
    private ?string $birth_date;
    private ?string $role;
    public function __construct($username=null,$first_name=null,$last_name=null,$email=null,$password=null,$birth_date=null,$role='user')
    {
        if($username !== null)
        {
            $this->username=$username;
            $this->first_name=$first_name;
            $this->last_name=$last_name;
            $this->email=$email;
            $this->password=$password;
            $this->birth_date=$birth_date;
            $this->role=$role;
        }
        
    }
    public function isAdmin()
    {
        return $this->role=='admin';
    }
    public function getId()
    {
        return $this->id;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getFirstName()
    {
        return $this->first_name;
    }
    public function getLastName()
    {
        return $this->last_name;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getBirthDate()
    {
        return $this->birth_date;
    }
    public function setUsername($username)
    {
        $this->username=$username;
    }
    public function setFirstName($first_name)
    {
        $this->first_name=$first_name;
    }
    public function setLastName($last_name)
    {
        $this->last_name=$last_name;
    }
    public function setEmail($email)
    {
        $this->email=$email;
    }
    public function setPassword($password)
    {
        $this->password=$password;
    }
    public function setBirthDate($birth_date)
    {
        $this->birth_date=$birth_date;
    }
    public function setRole($role)
    {
        $this->role=$role;
    }
    public function getRole()
    {
        return $this->role;
    }

}
?>