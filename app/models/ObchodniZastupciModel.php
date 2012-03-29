<?php

/*
 * Model for Obchodni Zastupci
 */

class ObchodniZastupciModel
{
    /**
     * Gets all ObchodniZastupce entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */ 
        public function getObchodniZastupce($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             return dibi::query(
                        'SELECT * FROM [obchodni_zastupci]
                         %if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('ObchodniZastupce');
        }
        
        /**
         * Adds new entity
         * @param ObchodniZastupce new obchodni zastupce
         * @return type false if fails otherwise id of inserted entity
         */
        public function addObchodniZastupce($obchodni_zastupce)
        {
            if (dibi::query("INSERT INTO [obchodni_zastupci] ", $obchodni_zastupce))
                return dibi::insertId();
            else
                return false;
        }
}

?>
