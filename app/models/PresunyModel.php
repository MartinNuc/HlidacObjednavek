<?php

use Nette\Diagnostics\Debugger;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PresunyModel
 *
 * @author mist
 */
class PresunyModel {
        public function getPresuny($order = NULL, $where = NULL, $offset = NULL, $limit = NULL)
        {
            $ret = dibi::query(
                        'SELECT *, presuny_automatu.id_zakaznik, zakaznici.nazev, zakaznici.adresa, date_format(datum, "%e. %c. %Y") as formatovane_datum FROM [presuny_automatu]
                            LEFT JOIN [zakaznici] USING (id_zakaznik)
                            LEFT JOIN [automaty] USING (id_automat)
                            WHERE 
                         %if', isset($where), '%and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Presun');
            return $ret;
        }
        
        /**
         * Adds new entity
         * @param Oblast new Oblast
         * @return type false if fails otherwise id of inserted entity
         */
        public function addPresun($presun)
        {
            if (dibi::query("INSERT INTO [presuny_automatu] ", $presun))
                return dibi::insertId();
            else
                return false;
        }
}

?>
