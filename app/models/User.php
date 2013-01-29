<?php

/**
 * Description of User entity
 *
 * @author mist
 */
class User extends DibiRow
{
    public function __construct($arr = array())
    {
        parent::__construct($arr);
    }
    
    /**
     * Deletes entity
     * @return bool result of deleting 
     */
    public function delete()
    {
        return dibi::query('DELETE FROM users WHERE [id_user]=%i', $this->id_user);
    }
    
    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        if (isset($this->id_user) == false)
                return false;
        if (isset($this->password) == true)
        {
            if ($this->password == "")
                unset($this->password);
            else
                $this->password = Authenticator::calculateHash($this->password, $this->username);
        }
        
        return dibi::query('UPDATE [users] SET', (array) $this, 'WHERE [id_user]=%i', $this->id_user); 
    }
    
    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new User();
        $res = dibi::query('SELECT * FROM [users] WHERE [id_user]=%i', $this->id_user)->setRowClass('User')->fetch(); 
        
        if (isset($res->id_user) == true)
        {
            $this->id_user = $res->id_user;
            $this->username = $res->username;
            $this->role = $res->role;
            return true;
        }
        return false;
    }
        
}

?>
