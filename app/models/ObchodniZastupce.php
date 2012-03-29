<?php

/*
 * Obchodni zastupce entity
 */
class ObchodniZastupce extends DibiRow
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
        return dibi::query('DELETE FROM [obchodni_zastupci] WHERE [id_obchodni_zastupce]=%i', $this->id_obchodni_zastupce);
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [obchodni_zastupci] SET', (array) $this, 'WHERE [id_obchodni_zastupce]=%i', $this->id_obchodni_zastupce); 
    }
    
    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new ObchodniZastupce();
        $res = dibi::query('SELECT * FROM [obchodni_zastupci] WHERE [id_obchodni_zastupce]=%i', $this->id_obchodni_zastupce)->setRowClass('ObchodniZastupce')->fetch(); 
        
        if (isset($res->id_obchodni_zastupce) == true)
        {
            $this->id_obchodni_zastupce = $res->id_obchodni_zastupce;
            $this->jmeno = $res->jmeno;
            $this->email = $res->email;
            $this->telefon = $res->telefon;
            return true;
        }
        return false;
    }
}

?>
