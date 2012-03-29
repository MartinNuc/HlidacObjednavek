<?php

/*
 * Smlouva entity
 */
class Smlouva extends DibiRow
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
        //return dibi::query('UPDATE [smlouvy] SET hidden=1 WHERE [id_smlouva]=%i', $this->id_smlouva); 
        return dibi::query('DELETE FROM smlouvy WHERE [id_smlouva]=%i', $this->id_smlouva);
    }

    /**
     * Saves changes to editted entity
     * @return bool result of UPDATE query 
     */
    public function save()
    {
        return dibi::query('UPDATE [smlouvy] SET', (array) $this, 'WHERE [id_smlouva]=%i', $this->id_smlouva); 
    }
    
    public static function setPOC($id_smlouva, $new_value = 0)
    {
        return dibi::query('UPDATE [smlouvy] SET `preferovany_poc` = %i WHERE [id_smlouva]=%i', $new_value, $id_smlouva); 
    }

    
    /**
     * gets entity information from DB based on id
     * @return bool false if fails otherwise true
     */
    public function fetch()
    {
        $res = new Smlouva();

        if (isset($this->id_smlouva))
            $res = dibi::query('SELECT * FROM [smlouvy] WHERE [id_smlouva]=%i', $this->id_smlouva)->setRowClass('Oblast')->fetch();
        else
            return false;
            
        if ($res == false)
            return false;

        $this->id_smlouva = $res->id_smlouva;
        $this->minimalni_odber = $res->minimalni_odber;
        $this->cislo_smlouvy = $res->cislo_smlouvy;
        $this->od = $res->od;
        $this->do = $res->do;
        $this->zpusob_platby = $res->zpusob_platby;
        $this->id_zakaznik = $res->id_zakaznik;
        $this->poc = $res->poc;    
        $this->preferovany_poc = $res->preferovany_poc;    
        return true;
    }
}

?>

