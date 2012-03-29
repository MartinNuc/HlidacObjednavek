<?php

/*
 * Zakaznik entity
 */
class Zakaznik extends DibiRow
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
        return dibi::query('UPDATE [zakaznici] SET hidden=1 WHERE [id_zakaznik]=%i', $this->id_zakaznik); 
        //return dibi::query('DELETE FROM oblasti WHERE [id_oblast]=%i', $this->id_oblast);
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [zakaznici] SET', (array) $this, 'WHERE [id_zakaznik]=%i', $this->id_zakaznik); 
    }
    
    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Zakaznik();
        $res = dibi::query('SELECT * FROM [zakaznici] WHERE hidden=0 and [id_zakaznik]=%i', $this->id_zakaznik)->setRowClass('Oblast')->fetch();
        
        if ($res == false)
            return false;

        $this->id_zakaznik = $res->id_zakaznik;
        $this->nazev = $res->nazev;
        $this->osobni_zakaznik = $res->osobni_zakaznik;
        $this->adresa = $res->adresa;
        $this->ico = $res->ico;
        $this->telefon = $res->telefon;
        $this->email = $res->email;
        $this->poznamka = $res->poznamka;
        
        return true;
    }
    
    /**
     * Returns items which this Zakaznik usually orders
     * @return DibiResult result
     */
    public function getZboziZakaznika()
    {
         return dibi::query('SELECT DISTINCT zakaznici_zbozi.ve_smlouve, zbozi.*, dph.*, id_zakaznici_zbozi FROM [zakaznici_zbozi]
             LEFT JOIN [zbozi] USING(id_zbozi) 
             LEFT JOIN [dph] USING ([id_dph])
             WHERE zbozi.hidden=0
             AND id_zakaznik=%i ORDER BY zbozi.nazev', $this->id_zakaznik)->setRowClass('Zbozi');
    }
    
    /**
     * Connects agreement with items
     * @param type $id_zakaznici_zbozi Id of item the customer has to order
     * @param type $new_value set new state (not needed)
     * @return bool result
     */
    public static function setVeSmlouve($id_zakaznici_zbozi, $new_value)
    {
        return dibi::query('UPDATE [zakaznici_zbozi] SET `ve_smlouve` = %i WHERE [id_zakaznici_zbozi]=%i', $new_value, $id_zakaznici_zbozi); 
    }

    /**
     * Adds item to this customer
     * @param int $id_zbozi Id of item added
     * @return bool result
     */
    public function pridatZboziZakaznikovi($id_zbozi)
    {
        if(isset($this->id_zakaznik)==false || isset($id_zbozi)==false)
            return false;
        
        $arr = array(
            'id_zakaznik' => $this->id_zakaznik,
            'id_zbozi'  => $id_zbozi,
        );
        if (dibi::query('INSERT INTO [zakaznici_zbozi]', $arr))
            return dibi::insertId();
        else
            return false;
    }
    
    /**
     * Removes item order information
     * @param int $id_zbozi Id of item we want to remove
     * @return bool result
     */
    public function deleteZbozi($id_zbozi)
    {
        return dibi::query('DELETE FROM zakaznici_zbozi WHERE [id_zakaznik]=%i AND [id_zakaznici_zbozi]=%i', $this->id_zakaznik, $id_zbozi);
    }
    
    /**
     * Gets all agreements of this customer
     * @return DibiResult result
     */
    public function getSmlouvyZakaznika()
    {
         return dibi::query('SELECT smlouvy.* FROM [smlouvy] LEFT JOIN [zakaznici] USING(id_zakaznik) WHERE 
                        id_zakaznik=%i', $this->id_zakaznik)->setRowClass('Smlouva');
    }
    
    
}

?>
