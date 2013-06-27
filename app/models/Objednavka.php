<?php
use Nette\Diagnostics\Debugger;
/*
 * Objednavka entity
 */
class Objednavka extends DibiRow
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
        // zbozi musime pri vymazani vratit na sklad
        $this->vratitZboziDoSkladu();
        // nejdrive musime smazat zbozi
        dibi::query('DELETE FROM [zbozi_objednavky] WHERE [id_objednavka]=%i', $this->id_objednavka);
        // pak samotnou objednavku
        return dibi::query('DELETE FROM [objednavky] WHERE [id_objednavka]=%i', $this->id_objednavka);
        
        //return dibi::query('UPDATE [objednavky] SET hidden=1 WHERE [id_objednavka]=%i', $this->id_objednavka); 
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        try {
        // smazem zbozi, ktere potom ale musime pridat
        dibi::query('DELETE FROM [zbozi_objednavky] WHERE [id_objednavka]=%i', $this->id_objednavka);
        
        dibi::query('UPDATE [objednavky] SET', (array) $this, 'WHERE [id_objednavka]=%i', $this->id_objednavka); 
        return true;
        }
        catch (DibiDriverException $e)
        {
            Debugger::log("Objednavka->save: " . dibi::$sql);
        }
        return false;
    }
    
    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function saveWithoutDelete()
    {
        try {
      
        dibi::query('UPDATE [objednavky] SET', (array) $this, 'WHERE [id_objednavka]=%i', $this->id_objednavka); 
        return true;
        }
        catch (DibiDriverException $e)
        {
            Debugger::log("Objednavka->save: " . dibi::$sql);
        }
        return false;
    }
    
    /**
     * Returns items from order to the warehouse
     */
    public function vratitZboziDoSkladu()
    {
        // polozky figurujici v objednavce
        $zbozi = dibi::query('SELECT id_zbozi, pocet FROM [zbozi_objednavky]
                WHERE [id_objednavka]=%i', $this->id_objednavka)->fetchPairs('id_zbozi', 'pocet');

        // vrati jednotlive polozky zpet do skladu
        foreach ($zbozi as $id_zbozi => $pocet)
        {
            dibi::query('UPDATE zbozi SET skladem = skladem + %f', $pocet, ' WHERE [id_zbozi]=%i', $id_zbozi);
        }
    }
    
    /**
     * Adss items to order
     * @param int $id_zbozi if of added item
     * @param int $pocet count of inserted items
     * @return bool result of operation 
     */
    public function pridatZboziDoObjednavky($id_zbozi, $pocet)
    {
        $values = array('id_zbozi' => $id_zbozi, 'pocet' => $pocet, 'id_objednavka' => $this->id_objednavka);
        if (dibi::query("INSERT INTO [zbozi_objednavky] ", $values))
        {
            $ret = dibi::insertId();
            dibi::query('UPDATE zbozi SET skladem = skladem - %f', $pocet, ' WHERE [id_zbozi]=%i', $id_zbozi);
            return $ret;
        }
        else
            return false;
    }
    
    /**
     * Checks if item is in order
     * @param int $id_zbozi Id of checked item
     * @param int $id_objednavka Id of order
     * @return type true if it is part of order, otherwise false
     */
    public static function jeZboziVObjednavce($id_zbozi, $id_objednavka)
    {
        $res = dibi::query('SELECT pocet FROM [zbozi_objednavky]
                WHERE [id_objednavka]=%i AND [id_zbozi]=%i', $id_objednavka, $id_zbozi)->setRowClass('Objednavka')->fetch();
        if ($res && count($res)>0)
            return $res->pocet;
        
        return false;
    }
    
    /**
     * Sets price and points of an order
     * @param float $cena_bez_dph calculated price without TAX
     * @param float $cena_s_dph calculated price with TAX
     * @param float $body calculated points
     * @return bool result of operation 
     */
    public function setCena($cena_bez_dph, $cena_s_dph, $body)
    {
        $arr = array(
            'cena_s_dph' => $cena_s_dph,
            'cena_bez_dph'  => $cena_bez_dph,
            'body'  => $body,
        );
        return dibi::query('UPDATE [objednavky] SET', $arr, 'WHERE [id_objednavka]=%i', $this->id_objednavka); 
    }
    
    /**
     * Gets last used code of order
     * @param string $prefix Prefix based on date
     * @return string last used code 
     */
    public static function getPosledniKod ($prefix)
    {
        $res = dibi::query('SELECT Count(*) FROM [objednavky]
                WHERE kod LIKE "' . $prefix . '%"')->setRowClass('Objednavka')->fetchSingle();
        //Debugger::log(dibi::$sql);
        return $res;
    }
    
    public function getZbozi()
    {
        $res = dibi::query('SELECT * FROM [zbozi_objednavky] LEFT JOIN [zbozi] USING (id_zbozi)
                WHERE [id_objednavka]=%i', $this->id_objednavka)->setRowClass('Zbozi');
        //Debugger::log(dibi::$sql);
        return $res;
    }
    
    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Objednavka();

        if (isset($this->id_objednavka))
            $res = dibi::query('SELECT date_format(objednavky.datum, "%e. %c. %Y") as formatovane_datum, zakaznici.nazev as zakaznik_nazev, oblasti.nazev as oblast_nazev, objednavky.* FROM [objednavky] LEFT JOIN [zakaznici] USING (id_zakaznik) LEFT JOIN [oblasti] USING (id_oblast)
                WHERE [id_objednavka]=%i', $this->id_objednavka)->setRowClass('Objednavka')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->id_objednavka = $res->id_objednavka;
        $this->id_zakaznik = $res->id_zakaznik;
        $this->id_oblast = $res->id_oblast;
        $this->oblast_nazev = $res->oblast_nazev;
        $this->zakaznik_nazev = $res->zakaznik_nazev;
        $this->kod = $res->kod;
        $this->datum = $res->datum;
        $this->formatovane_datum = $res->formatovane_datum;
        $this->poznamka = $res->poznamka;
        $this->cena_bez_dph = $res->cena_bez_dph;
        $this->cena_s_dph = $res->cena_s_dph;
        $this->hledani_vyrobni_cislo = $res->hledani_vyrobni_cislo;
        $this->hledani_bmb = $res->hledani_bmb;
        $this->id_smlouva = $res->id_smlouva;
        $this->body = $res->body;

        return true;
    }
}

?>
