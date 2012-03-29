<?php

/*
 * Kontakt entity
 */
class Kontakt extends DibiRow
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
        //return dibi::query('UPDATE [automaty] SET hidden=1 WHERE [id_automat]=%i', $this->id_automat); 
        return dibi::query('DELETE FROM kontakty WHERE [id_kontakt]=%i', $this->id_kontakt);
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [kontakty] SET', (array) $this, 'WHERE [id_kontakt]=%i', $this->id_kontakt); 
    }
    
    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Kontakt();

        if (isset($this->id_kontakt))
            $res = dibi::query('SELECT * FROM [kontakty]
                WHERE [id_kontakt]=%i', $this->id_kontakt)->setRowClass('Kontakt')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->jmeno = $res->jmeno;
        $this->telefon = $res->telefon;
        $this->email = $res->email;
        $this->poznamka = $res->poznamka;
        
        return true;
    }
}

?>

