<?php

/*
 * Technik entity
 */
class Technik extends DibiRow
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
        return dibi::query('DELETE FROM technici WHERE [id_technik]=%i', $this->id_technik);
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [technici] SET', (array) $this, 'WHERE [id_technik]=%i', $this->id_technik); 
    }

    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Technik();
        $res = dibi::query('SELECT * FROM [technici] WHERE [id_technik]=%i', $this->id_technik)->setRowClass('Technik')->fetch(); 
        
        if (isset($res->id_technik) == true)
        {
            $this->id_technik = $res->id_technik;
            $this->jmeno = $res->jmeno;
            $this->prijmeni = $res->prijmeni;
            $this->id_oblast = $res->id_oblast;
            return true;
        }
        return false;
    }
    
}

?>
