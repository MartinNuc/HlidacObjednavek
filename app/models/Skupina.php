<?php

/*
 * Oblast entity
 */
class Skupina extends DibiRow
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
        return dibi::query('UPDATE [skupiny] SET hidden=1 WHERE [id_skupina]=%i', $this->id_skupina); 
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [skupiny] SET', (array) $this, 'WHERE [id_skupina]=%i', $this->id_skupina); 
    }

    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Skupina();

        if (isset($this->id_skupina))
            $res = dibi::query('SELECT * FROM [skupiny] WHERE hidden=0 and [id_skupina]=%i', $this->id_skupina)->setRowClass('Skupina')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->id_skupina = $res->id_skupina;
        $this->nazev = $res->nazev;
        
        return true;
    }
}

?>
