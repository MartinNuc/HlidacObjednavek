<?php

/*
 * Model to work with users
 */

class UsersModel
{
    /**
     * Gets all Users entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */ 
        public function getUsers($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             return dibi::query(
                        'SELECT * FROM users  
                         %if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('User');
        }
        
        /**
         * Adds new entity
         * @param User new User
         * @return type false if fails otherwise id of inserted entity
         */
        public function addUser($user)
        {
            if (isset($user->password) == true)
            {
                $user->password = Authenticator::calculateHash($user->password, $user->username);
            }
            else
                return false;
            
            if (dibi::query("INSERT INTO [users] ", $user))
                return dibi::insertId();
            else
                return false;
        }
}

?>
