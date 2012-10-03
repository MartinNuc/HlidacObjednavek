<?php

/**
 * Automat's entitiy
 */
class Automat extends DibiRow
{   
    public function __construct($arr = array())
    {
        parent::__construct($arr);
    }

    /**
     * Deletes automat from database
     * @return bool result of delete query
     */
    public function delete()
    {
        //return dibi::query('UPDATE [automaty] SET hidden=1 WHERE [id_automat]=%i', $this->id_automat); 
        return dibi::query('DELETE FROM [automaty] WHERE [id_automat]=%i', $this->id_automat);
    }
    
    /**
     * Moves automat into warehouse
     * @return bool result of UPDATE query 
     */
    public function doSkladu()
    {
        dibi::query("INSERT INTO [presuny_automatu] ", array('id_automat' => $this->id_automat, 'id_zakaznik' => 0, 'datum' => $date));
        return dibi::query('UPDATE [automaty] SET id_oblast=0, id_zakaznik=0, adresa="", umisteni="" WHERE [id_automat]=%i', $this->id_automat); 
        //return dibi::query('DELETE FROM oblasti WHERE [id_oblast]=%i', $this->id_oblast);
    }
    
    /**
     * Removes connection between contact and automat
     * @param int $id_automat_kontakt Id of connection
     * @return bool result of DELETE query 
     */    
    public static function odpriraditKontakt($id_automat_kontakt)
    {
        return dibi::query('DELETE FROM [automat_kontakt] WHERE [id_automat_kontakt]=%i', $id_automat_kontakt);
    }
    
    /**
     * Connects contact with this automat
     * @param int $id_kontakt Id of contact we want to connect
     * @return bool result of connecting process 
     */    
    public function priraditKontakt ($id_kontakt)
    {
        $res = dibi::query('SELECT * FROM [automat_kontakt]
                WHERE id_automat=%i AND [id_kontakt]=%i', $this->id_automat, $id_kontakt)->fetch();
        
        if (count($res) > 1)
        {
            // kontakt je jiz prirazen
            return false;
        }
        
        if (dibi::query("INSERT INTO [automat_kontakt] ", array('id_automat' => $this->id_automat, 'id_kontakt' => $id_kontakt)))
            return dibi::insertId();
        else
            return false;
    }

    /**
     * Updates changes of automat in database
     * @return bool result of UPDATE 
     */
    public function save()
    {
        $tmp = new Automat();
        $tmp->id_automat = $this->id_automat;
        $tmp->fetch();
        if ($tmp->id_zakaznik != $this->id_zakaznik)
        {
            $date = date('Y-m-d');
            dibi::query("INSERT INTO [presuny_automatu] ", array('id_automat' => $this->id_automat, 'id_zakaznik' => $this->id_zakaznik, 'datum' => $date));
        }
        return dibi::query('UPDATE [automaty] SET', (array) $this, 'WHERE [id_automat]=%i', $this->id_automat); 
    }
    
    /**
     * Gets rest of properties of automat from database according to set id_automat
     * @return bool result of retriving operation 
     */
    public function fetch()
    {
        $res = new Automat();

        if (isset($this->id_automat))
            $res = dibi::query('SELECT zakaznici.nazev as zakaznik_nazev, zakaznici.adresa as zakaznik_adresa, zakaznici.*, automaty.*, oblasti.nazev as oblast_nazev FROM [automaty] LEFT JOIN [zakaznici] USING (id_zakaznik) LEFT JOIN [oblasti] USING (id_oblast)
                WHERE [id_automat]=%i', $this->id_automat)->setRowClass('Automat')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->id_automat = $res->id_automat;
        $this->adresa = $res->adresa;
        $this->nazev = $res->nazev;
        $this->bmb = $res->bmb;
        $this->umisteni = $res->umisteni;
        $this->vyrobni_cislo = $res->vyrobni_cislo;
        $this->layout = $res->layout;
        
        // zakaznik
        $this->id_zakaznik = $res->id_zakaznik;
        $this->zakaznik_nazev = $res->zakaznik_nazev;
        $this->zakaznik_adresa = $res->zakaznik_adresa;
        $this->zakaznik_email = $res->email;
        
        // oblast
        $this->oblast_nazev = $res->oblast_nazev;
        $this->id_oblast = $res->id_oblast;
        
        return true;
    }
}

?>
