<?php
namespace Home\Event;


class UserEvent  
{
    public function login()
    {
    	echo 'login!<br>';
    }
    
    public function logout()
    {
        echo 'Logout!<br>';
    }
}
