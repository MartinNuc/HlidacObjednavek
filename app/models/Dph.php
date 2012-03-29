<?php
/**
 * Description of Dph
 *
 * @author mist
 */
class Dph  extends DibiRow
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
        //return dibi::query('UPDATE [oblasti] SET hidden=1 WHERE [id_oblast]=%i', $this->id_oblast); 
        return dibi::query('DELETE FROM dph WHERE [id_dph]=%i', $this->id_dph);
    }

    /**
     * Saves changes to editted DPH entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [dph] SET', (array) $this, 'WHERE [id_dph]=%i', $this->id_dph); 
    }
    
    /**
     * gets DPH informations from DB based on id_dph
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Dph();

        if (isset($this->id_dph))
            $res = dibi::query('SELECT * FROM [dph] WHERE [id_dph]=%i', $this->id_dph)->setRowClass('Dph')->fetch();
        else if (isset($this->dph))
            $res = dibi::query('SELECT * FROM [dph] WHERE [dph]=%s', $this->dph)->setRowClass('Dph')->fetch(); 
        else return false;
        
        if ($res == false)
            return false;

        $this->id_dph = $res->id_dph;
        $this->dph = $res->dph;
        
        return true;
    }
}

?>
