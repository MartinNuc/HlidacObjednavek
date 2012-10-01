<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Presun
 *
 * @author mist
 */
class Presun  extends DibiRow
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
        return dibi::query('DELETE FROM [presuny_automatu] WHERE [id_presun]=%i', $this->id_presun); 
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [presuny_automatu] SET', (array) $this, 'WHERE [id_presun]=%i', $this->id_presun); 
    }

    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Presun();

        if (isset($this->id_presun))
            $res = dibi::query('SELECT * FROM [presuny_automatu] WHERE [id_presun]=%i', $this->id_presun)->setRowClass('Presun')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->id_presun = $res->id_presun;
        $this->id_zakaznik = $res->id_zakaznik;
        $this->id_automat = $res->id_automat;
        $this->datum = $res->datum;
        
        return true;
    }
}

?>
