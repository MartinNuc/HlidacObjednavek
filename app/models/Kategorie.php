<?php

/*
 * Kategorie entity
 */
class Kategorie extends DibiRow
{   
    public function __construct($arr = array())
    {
        parent::__construct($arr);
    }

    /**
     * Deletes category
     * @return bool result of deleting 
     */
    public function delete()
    {
        //return dibi::query('UPDATE [oblasti] SET hidden=1 WHERE [id_oblast]=%i', $this->id_oblast); 
        return dibi::query('DELETE FROM kategorie WHERE [id_kategorie]=%i', $this->id_kategorie);
    }

    /**
     * Saves changes to editted Kategorie entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [kategorie] SET', (array) $this, 'WHERE [id_kategorie]=%i', $this->id_kategorie); 
    }
    
    /**
     * gets Kategorie informations from DB based on id_kategorie
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Kategorie();

        if (isset($this->id_kategorie))
            $res = dibi::query('SELECT * FROM [kategorie] WHERE [id_kategorie]=%i', $this->id_kategorie)->setRowClass('Kategorie')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->id_kategorie = $res->id_kategorie;
        $this->nazev = $res->nazev;
        
        return true;
    }
}

?>
