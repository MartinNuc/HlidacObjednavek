<?php

use Nette\Diagnostics\Debugger;

/*
 * Model to work with Zbozi
 */

class ZboziModel
{
        /**
         * Get Zbozi entity
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @param string $filtrLike used for filtering through LIKE operator
         * @return DibiResult result
         */
        public function getZbozi($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtrLike = NULL, $filtr_kategorie = NULL)
        {
             $ret = dibi::query(
                        'SELECT dph.dph as dph_cislo, dph.dph as dph, kategorie.nazev as kategorie_nazev, zbozi.* FROM [zbozi] LEFT JOIN [dph] USING (id_dph) LEFT JOIN [kategorie] USING (id_kategorie) WHERE hidden=0 
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($filtrLike) && $filtrLike!="", ' AND (zbozi.nazev LIKE %s', isset($filtrLike) ? "%" .$filtrLike."%" : '',
                        'OR kategorie.nazev LIKE "%'.$filtrLike.'%"', 
                        'OR zbozi.zkratka LIKE "%'.$filtrLike.'%"', 
                        ')%end',
                        '%if', isset($filtr_kategorie), isset($filtr_kategorie) ? "AND (".$filtr_kategorie.")" : "", '%end ',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Zbozi');
             return $ret;
        }

      
        /**
         * Get Zbozi entity
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @param string $filtrLike used for filtering through LIKE operator
         * @return DibiResult result
         * 
         * SELECT zkratka, sum(pocet) FROM objednavky o LEFT JOIN zbozi_objednavky USING (id_objednavka) LEFT JOIN zbozi USING (id_zbozi) WHERE id_zakaznik=1 GROUP BY zkratka;
         */
        public function getZboziOdDo($order = NULL, $where = NULL, $od = NULL, $do = NULL, $filtr_kategorie = NULL, $filtr_oblasti = NULL)
        {
            try {
             $ret = dibi::query(
                        'SELECT id_zbozi, zkratka, sum(pocet) as pocet FROM objednavky o LEFT JOIN zbozi_objednavky USING (id_objednavka) LEFT JOIN zbozi USING (id_zbozi) WHERE hidden=0
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                         '%if', isset($filtr_kategorie), isset($filtr_kategorie) ? "AND (".$filtr_kategorie.")" : "", '%end ',
                         '%if', isset($filtr_oblasti), isset($filtr_oblasti) ? "AND (".$filtr_oblasti.")" : "", '%end ',
                         'AND (o.datum>="' . $od . '"',
                         ' AND o.datum<="' . $do . '")',
                        ' GROUP BY zkratka',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end'
                    )->setRowClass('Zbozi');
            }
            catch (DibiException $e)
            {
                Debugger::log("getZboziOdDo: " . dibi::$sql);
            }
             return $ret;
        }
        
        public function getZboziPodleSmlouvy($id_zakaznik)
        {
             $res = dibi::query(
                     'SELECT id_zakaznik, GROUP_CONCAT(zkratka SEPARATOR "+") as zkratka FROM [zakaznici_zbozi] LEFT JOIN zbozi USING (id_zbozi)
                        WHERE 
                          ve_smlouve=1 ',
                          '%if', isset($id_zakaznik), 'AND %and', isset($id_zakaznik) ? $id_zakaznik : array(), '%end',
                     "GROUP BY id_zakaznik ORDER BY id_zakaznik"
                     )->setRowClass('Zbozi');
             return $res; 
        }

        
        /**
         * Adds new entity
         * @param Zbozi new Zbozi
         * @return type false if fails otherwise id of inserted entity
         */
        public function addZbozi($zbozi)
        {
            if (isset($zbozi->dph) == false && isset($zbozi->dph_cislo))
            {
                $dph = new Dph();
                $dph->dph = $zbozi->dph_cislo;
                if ($dph->fetch())
                    $zbozi->id_dph = $dph->id_dph;
                else
                    return false;
            }
            
            if (isset($zbozi->dph_cislo))
                unset($zbozi->dph_cislo);
            if (dibi::query("INSERT INTO [zbozi] ", $zbozi))
                return dibi::insertId();
            else
                return false;
        }
}

?>
