<?php

/*
 * Model to work with Skupiny entities
 */

class SkupinyModel
{
    /**
     * Gets all Oblasti entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */ 
        public function getSkupiny($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             return dibi::query(
                        'SELECT skupiny.* FROM [skupiny] WHERE hidden=0 
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Skupina');
        }
        
        /**
         * Adds new entity
         * @param Oblast new Oblast
         * @return type false if fails otherwise id of inserted entity
         */
        public function addSkupina($skupina)
        {
            if (dibi::query("INSERT INTO [skupiny] ", $skupina))
                return dibi::insertId();
            else
                return false;
        }
}

?>
