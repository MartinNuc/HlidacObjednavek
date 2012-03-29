<?php

/*
 * Model to work with Smlouva entities
 */

class SmlouvyModel
{
    /**
     * Gets all Smlouva entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */ 
        public function getSmlouvy($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             return dibi::query(
                        'SELECT * FROM [smlouvy]
                         %if', isset($where), ' WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Smlouva');
        }
        
       
        /**
         * Adds new entity
         * @param Smlouva new Smlouva
         * @return type false if fails otherwise id of inserted entity
         */
        public function addSmlouva($smlouva)
        {
            if (dibi::query("INSERT INTO [smlouvy] ", $smlouva))
                return dibi::insertId();
            else
                return false;
        }
}

?>
