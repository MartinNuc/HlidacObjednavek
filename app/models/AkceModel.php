<?php
use Nette\Diagnostics\Debugger;
/**
 * Description of DphModel
 *
 * @author mist
 */
class AkceModel {
    /**
     * Gets all DPH from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */
        public function getAkce($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             $res = dibi::query(
                        'SELECT *, date_format(opravy.datum, "%e. %c. %Y") as formatovane_datum FROM [akce] left join [opravy] using (id_oprava)
                            left join [skupiny] using (id_skupina) ',
                        '%if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Akce');
             return $res;
        }
               
        public function getDeletedSkupinyOfAkce($order = NULL, $id_oprava, $offset = NULL, $limit = NULL)
        {
             $res = dibi::query(
                        'SELECT DISTINCT skupiny.* FROM [akce] LEFT JOIN [skupiny] USING (id_skupina) WHERE hidden=1 and id_oprava=', $id_oprava, 
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Skupina');
             return $res;
        }
        
        /**
         * Adds new DPH value
         * @param Dph new DPH
         * @return type false if fails otherwise id of inserted entity
         */
        public function addAkce($akce)
        {
            if (dibi::query("INSERT INTO [akce] ", $akce))
                return dibi::insertId();
            else
                return false;
        }
}

?>
