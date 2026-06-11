<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/entities/User.php';
require_once __DIR__ . '/../models/UserModel.php';
class AuthController{
    public function verifyUser()
    {
        $userModel = new UserModel();
        $user = $userModel->getUserByUsernameOrEmail($_POST['username']);
        if (!$user) {
            return 'no_user';
        }
        if (!password_verify($_POST['password'], $user->getPassword())) {
            return 'wrong_password';
        }
        return 'success';
    }
    public function userRegister()
    {
            $username=$_POST['username'];
            $first_name=$_POST['first_name'];
            $last_name=$_POST['last_name'];
            $email=$_POST['email'];
            $p1=$_POST['confirm_password'];
            $p2=$_POST['password'];
            $birth_date=$_POST['birth_date'];
            $userModel=new UserModel();
            if($userModel->uniqueEmail($email) && $userModel->uniqueUsername($username) && $p1===$p2 && ((new DateTime($birth_date))->diff(new DateTime('today'))->y)>12 )
            {
                $password=password_hash($p2,PASSWORD_DEFAULT);
                $userModel->addUser($username,$email,$first_name,$last_name,$password,$birth_date);
                return true;
            }else{
                return false;
            }
            


    }
}
?>