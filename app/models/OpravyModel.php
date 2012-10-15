<?php
use Nette\Diagnostics\Debugger;

/**
 * Description of DphModel
 *
 * @author mist
 */
class OpravyModel {
    /**
     * Gets all DPH from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */
        public function getOpravy($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
             return dibi::query(
                        'SELECT id_oprava, id_automat,  date_format(datum, "%e. %c. %Y") as formatovane_datum, sum(cena*pocet) as cena FROM [opravy] left join [akce] using (id_oprava) ',
                        '%if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        'GROUP BY [opravy.id_oprava]',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Oprava');
        }
        
        public function getOpravyContext($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtr_oblasti = NULL)
        {
            try {
             $ret = dibi::query(
                        'SELECT id_oprava, id_automat, automaty.nazev, date_format(datum, "%e. %c. %Y") as formatovane_datum, sum(cena*pocet) as cena 
                            FROM [opravy] ',
                            'left join [akce] using (id_oprava) ',
                            'left join [automaty] using (id_automat) ',
                        '%if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($filtr_oblasti), isset($filtr_oblasti) ? "AND (" . $filtr_oblasti . ")" : "", '%end ',
                        'GROUP BY [opravy.id_oprava]',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Oprava');
            }
            catch (DibiException $e)
            {
                Debugger::log("getOpravyContext: " . Dibi::$sql);
                return null;
            }
             return $ret;
        }
        
        
        /**
         * Adds new DPH value
         * @param Dph new DPH
         * @return type false if fails otherwise id of inserted entity
         */
        public function addOprava($oprava)
        {
            if (dibi::query("INSERT INTO [opravy] ", $oprava))
                return dibi::insertId();
            else
                return false;
        }
}

?>
