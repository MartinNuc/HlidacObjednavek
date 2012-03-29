<?php

/*
 * Model to work with Technik entity
 */

class TechniciModel
{
    /**
     * Gets all Technik entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */ 
        public function getTechnici($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             return dibi::query(
                        'SELECT * FROM [technici] LEFT JOIN [oblasti] USING (id_oblast)
                         %if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Technik');
        }
        
        /**
         * Adds new entity
         * @param Technik new Technik
         * @return type false if fails otherwise id of inserted entity
         */
        public function addTechnik($technik)
        {
            if (isset($technik->oblast) == false && isset($technik->nazev_oblasti))
            {
                $oblast = new Oblast();
                $oblast->nazev = $technik->nazev_oblasti;
                if ($oblast->fetch())
                    $technik->id_oblast = $oblast->id_oblast;
                else
                    return false;
            }
            
            if (isset($technik->nazev_oblasti))
                unset($technik->nazev_oblasti);
            if (dibi::query("INSERT INTO [technici] ", $technik))
                return dibi::insertId();
            else
                return false;
        }
}

?>
