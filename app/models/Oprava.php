<?php
/**
 * Description of Dph
 *
 * @author mist
 */
class Oprava extends DibiRow
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
        return dibi::query('DELETE FROM opravy WHERE [id_oprava]=%i', $this->id_oprava);
    }

    /**
     * Saves changes to editted DPH entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [opravy] SET', (array) $this, 'WHERE [id_oprava]=%i', $this->id_oprava); 
    }
    
    /**
     * gets DPH informations from DB based on id_dph
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Oprava();

        if (isset($this->id_oprava))
            $res = dibi::query('SELECT *, date_format(datum, "%e. %c. %Y") as formatovane_datum FROM [opravy] WHERE [id_oprava]=%i', $this->id_oprava)->setRowClass('Oprava')->fetch();
        else return false;
        
        if ($res == false)
            return false;

        $this->id_oprava = $res->id_oprava;
        $this->id_automat = $res->id_automat;
        $this->datum = $res->datum;
        $this->formatovane_datum = $res->formatovane_datum;
        
        return true;
    }
}

?>
