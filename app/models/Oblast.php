<?php

/*
 * Oblast entity
 */
class Oblast extends DibiRow
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
        //return dibi::query('UPDATE [oblasti] SET hidden=1 WHERE [id_oblast]=%i', $this->id_oblast); 
        return dibi::query('DELETE FROM oblasti WHERE [id_oblast]=%i', $this->id_oblast);
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [oblasti] SET', (array) $this, 'WHERE [id_oblast]=%i', $this->id_oblast); 
    }

    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Oblast();

        if (isset($this->id_oblast))
            $res = dibi::query('SELECT obchodni_zastupci.id_obchodni_zastupce, obchodni_zastupci.jmeno as oz_jmeno, obchodni_zastupci.telefon as oz_telefon, obchodni_zastupci.email as oz_email, oblasti.* FROM [oblasti] LEFT JOIN obchodni_zastupci USING (id_obchodni_zastupce) WHERE hidden=0 and [id_oblast]=%i', $this->id_oblast)->setRowClass('Oblast')->fetch();
        else if (isset($this->nazev))
            $res = dibi::query('SELECT obchodni_zastupci.id_obchodni_zastupce, obchodni_zastupci.jmeno as oz_jmeno, obchodni_zastupci.telefon as oz_telefon, obchodni_zastupci.email as oz_email, oblasti.* FROM [oblasti] LEFT JOIN obchodni_zastupci USING (id_obchodni_zastupce) WHERE hidden=0 and [nazev]=%s', $this->nazev)->setRowClass('Oblast')->fetch(); 
        else return false;
        
        if ($res == false)
            return false;

        $this->id_oblast = $res->id_oblast;
        $this->id_obchodni_zastupce = $res->id_obchodni_zastupce;
        $this->oz_jmeno = $res->oz_jmeno;
        $this->oz_telefon = $res->oz_telefon;
        $this->oz_email = $res->oz_email;
        $this->nazev = $res->nazev;
        
        return true;
    }
}

?>
