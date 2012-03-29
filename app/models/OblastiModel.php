<?php

/*
 * Model to work with Oblasti entities
 */

class OblastiModel
{
    /**
     * Gets all Oblasti entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */ 
        public function getOblasti($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             return dibi::query(
                        'SELECT obchodni_zastupci.jmeno as oz_jmeno, oblasti.* FROM [oblasti] LEFT JOIN [obchodni_zastupci] USING (id_obchodni_zastupce) WHERE hidden=0 
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Oblast');
        }
        
        /**
         * Adds new entity
         * @param Oblast new Oblast
         * @return type false if fails otherwise id of inserted entity
         */
        public function addOblast($oblast)
        {
            if (dibi::query("INSERT INTO [oblasti] ", $oblast))
                return dibi::insertId();
            else
                return false;
        }
}

?>
