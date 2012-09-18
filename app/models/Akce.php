<?php
/**
 * Description of Dph
 *
 * @author mist
 */
class Akce extends DibiRow
{
    public function __construct($arr = array())
    {
        parent::__construct($arr);
    }

    /**
     * Deletes DPH from DB
     * @return bool result of deleting 
     */
    public function delete()
    {
        return dibi::query('DELETE FROM akce WHERE [id_akce]=%i', $this->id_akce);
    }

    /**
     * Saves changes to editted DPH entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [akce] SET', (array) $this, 'WHERE [id_akce]=%i', $this->id_akce); 
    }
    
    /**
     * gets DPH informations from DB based on id_dph
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Akce();

        if (isset($this->id_akce))
            $res = dibi::query('SELECT * FROM [akce] WHERE [id_akce]=%i', $this->id_akce)->setRowClass('Akce')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->id_akce = $res->id_akce;
        $this->popis = $res->popis;
        $this->cena = $res->cena;
        $this->id_skupina = $res->id_skupina;
        $this->id_oprava = $res->id_oprava;
        
        return true;
    }
}

?>
