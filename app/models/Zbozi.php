<?php
use Nette\Diagnostics\Debugger;
/*
 * Zbozi entity
 */
class Zbozi extends DibiRow
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
        //return dibi::query('DELETE FROM zbozi WHERE [id_zbozi]=%i', $this->id_zbozi);
        return dibi::query('UPDATE [zbozi] SET hidden=1, zkratka="" WHERE [id_zbozi]=%i', $this->id_zbozi); 
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        if (isset($this->id_dph) == false && isset($this->dph_cislo))
        {
            $dph = new Dph();
            $dph->dph = $this->dph_cislo;
            if ($dph->fetch())
                $this->id_dph = $dph->id_dph;
            else
                return false;
        }

        if (isset($this->dph_cislo))
            unset($this->dph_cislo);
        return dibi::query('UPDATE [zbozi] SET', (array) $this, 'WHERE [id_zbozi]=%i', $this->id_zbozi); 
    }
    
    /**
     * When new items arrive to the warehouse
     * @param int $pocet Count
     * @return bool result
     */
    public function pridejNaSklad($pocet = 1)
    {
        $ret = dibi::query('UPDATE zbozi SET skladem = skladem + %f', $pocet, ' WHERE [id_zbozi]=%i', $this->id_zbozi);
        //Debugger::log(Dibi::$sql);
        return $ret;
    }

    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Zbozi();
        
        if (isset($this->id_zbozi) == true)
            $res = dibi::query('SELECT dph.dph as dph_cislo, kategorie.nazev as kategorie_nazev, zbozi.* FROM [zbozi] LEFT JOIN [dph] USING (id_dph) LEFT JOIN [kategorie] USING (id_kategorie) WHERE [id_zbozi]=%i', $this->id_zbozi)->setRowClass('Zbozi')->fetch(); 
        if (isset($this->nazev) == true)
            $res = dibi::query('SELECT dph.dph as dph_cislo, kategorie.nazev as kategorie_nazev, zbozi.* FROM [zbozi] LEFT JOIN [dph] USING (id_dph) LEFT JOIN [kategorie] USING (id_kategorie) WHERE zbozi.hidden=0 AND [zbozi.nazev]=%s', $this->nazev)->setRowClass('Zbozi')->fetch(); 
        if (isset($this->zkratka) == true)
            $res = dibi::query('SELECT dph.dph as dph_cislo, kategorie.nazev as kategorie_nazev, zbozi.* FROM [zbozi] LEFT JOIN [dph] USING (id_dph) LEFT JOIN [kategorie] USING (id_kategorie) WHERE zbozi.hidden=0 AND [zkratka]=%s', $this->zkratka)->setRowClass('Zbozi')->fetch(); 

        if (isset($res->id_zbozi) == true)
        {
            $this->id_zbozi = $res->id_zbozi;
            $this->zkratka = $res->zkratka;
            $this->nazev = $res->nazev;
            $this->skladem = $res->skladem;
            $this->id_dph = $res->id_dph;
            $this->nestle = $res->nestle;
            $this->kategorie_nazev = $res->kategorie_nazev;
            $this->id_kategorie = $res->id_kategorie;
            $this->prodejni_cena = $res->prodejni_cena;
            $this->nakupni_cena = $res->nakupni_cena;
            $this->body = $res->body;
            $this->sapcode = $res->sapcode;
            return true;
        }
        return false;
    }
    
}

?>
