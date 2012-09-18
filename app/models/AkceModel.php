<?php

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
             return dibi::query(
                        'SELECT * FROM [akce] ',
                        '%if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Dph');
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
