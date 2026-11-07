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
    /** Creates the account and returns true, or returns the first problem as a message. */
    public function userRegister()
    {
            // Limits follow the users table (username and names VARCHAR(50), email VARCHAR(100)).
            $problem = validate_fields([
                'username'   => ['Username', 'text', 3, 50],
                'first_name' => ['First name', 'text', 1, 50],
                'last_name'  => ['Last name', 'text', 1, 50],
                'email'      => ['Email', 'text', 3, 100],
            ], $clean);
            if ($problem) return $problem;
            if (!filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) return 'Enter a valid email address.';

            $p1 = (string) ($_POST['confirm_password'] ?? '');
            $p2 = (string) ($_POST['password'] ?? '');
            if (mb_strlen($p2) < 8) return 'Password must be at least 8 characters long.';
            if ($p1 !== $p2) return 'The two passwords do not match.';

            $birth_date = (string) ($_POST['birth_date'] ?? '');
            $birth = DateTime::createFromFormat('Y-m-d', $birth_date);
            if (!$birth || $birth->format('Y-m-d') !== $birth_date || $birth > new DateTime('today')) {
                return 'Enter a valid birth date.';
            }
            if ($birth->diff(new DateTime('today'))->y < 13) return 'You must be 13 or older to create an account.';

            $userModel=new UserModel();
            if (!$userModel->uniqueUsername($clean['username'])) return 'That username is already taken.';
            if (!$userModel->uniqueEmail($clean['email'])) return 'An account with that email already exists.';

            $password=password_hash($p2,PASSWORD_DEFAULT);
            $userModel->addUser($clean['username'],$clean['email'],$clean['first_name'],$clean['last_name'],$password,$birth_date);
            return true;
    }
}
?>