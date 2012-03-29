<?php
use Nette\Diagnostics\Debugger;

/*
 * Kategorie model to work with Kategorie entities
 */

class KategorieModel
{
    /**
     * Gets all Kategorie from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */
        public function getKategorie($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtr_kategorie = NULL)
        {
             $ret = dibi::query(
                        'SELECT * FROM [kategorie] WHERE 1=1
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($filtr_kategorie), isset($filtr_kategorie) ? "AND (".$filtr_kategorie.")" : "", '%end ',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Kategorie');
             return $ret;
        }
        
        
        /**
         * Adds new Kategorie entity
         * @param Kategorie new Kategorie
         * @return type false if fails otherwise id of inserted kategorie
         */
        public function addKategorie($kategorie)
        {
            if (dibi::query("INSERT INTO [kategorie] ", $kategorie))
                return dibi::insertId();
            else
                return false;
        }
}

?>
