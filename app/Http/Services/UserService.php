<?php 

namespace App\Http\Services;

use App\Models\Users;
use Carbon\Carbon;

class UserService
{

    /**
     * Object of User class for working with DB.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new instance of UserService.
     *
     * @param User $user
     * @return void
     */
    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Store user data into the DB .
     *
     * @param array $userData
     * @return User
     */
    public function createUser($userData)
    {
        return $this->user->create($userData);
    }

    /**
     * Get user by primary key.
     *
     * @param integer $id
     * @return $user
     */
    public function getUserByPK($email)
    {
        return $this->user->with(['numbers'])->find($email);
    }

    /**
     * Get user by email.
     *
     * @param string $email
     * @return User
     */
    public function getUserByEmail($email)
    {
        return $this->user->where('email', $email)->where('idrol', '1')->with(['numbers'])->first();
    }

    /**
     * Update users data.
     *
     * @param integer $id
     * @param array $userData
     * @return bool
     */
    public function updateUser($email, $userData)
    {
        return $this->getUserByPK($email)->update($userData);
    }
  
 
    
    /**
     * Get user by password token.
     *
     * @param string $token
     * @return User
     */
    public function getUserByPasswordToken($token)
    {
        return $this->user->where('remember_token', $token)->where('confirmed', '!=', 1)->first();
    }

  





   

}
