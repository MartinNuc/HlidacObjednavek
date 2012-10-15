<?php
use Nette\Diagnostics\Debugger;

/*
 * Model to work with Zakaznik
 */

class ZakazniciModel
{
    /**
     * Gets all Users entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @param string $filtrLike filter to filter result by name using LIKE operator
     * @return DibiResult result 
     */ 
        public function getZakazniky($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtrLike = NULL)
        {
             return dibi::query(
                        'SELECT * FROM [zakaznici] WHERE hidden=0 
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($filtrLike) && $filtrLike!="", ' AND nazev LIKE %s', isset($filtrLike) ? "%" .$filtrLike."%" : '', '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Zakaznik');
        }
        
        public function getZakaznikyContext($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtrLike = NULL)
        {
            try {
                 $res = dibi::query(
                            'SELECT * FROM [zakaznici] 
                                LEFT JOIN [smlouvy] USING (id_zakaznik)
                                WHERE hidden=0 
                             %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                            '%if', isset($filtrLike) && $filtrLike!="", ' AND nazev LIKE %s', isset($filtrLike) ? "%" .$filtrLike."%" : '', '%end',
                            '%if', isset($order), 'ORDER BY %by', $order, '%end',
                            '%if', isset($limit), 'LIMIT %i %end', $limit,
                            '%if', isset($offset), 'OFFSET %i %end', $offset
                        )->setRowClass('Zakaznik');
                 return $res;
            }
            catch (DibiException $e)
            {
                Debugger::log("getZakaznikyContext: " . dibi::$sql);
            }
        }
        
        /**
         * Gets Zakaznik entities in specific area
         * @param type $id_oblast Id of area we want to retrieve data from
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @param string $filtrLike filter to filter result by name using LIKE operator
         * @return DibiResult result
         */
        public function getZakaznikyVOblasti($id_oblast, $order = NULL, $offset = NULL, $limit = NULL, $filtrLike = NULL, $where = NULL)
        {
             $ret = dibi::query(
                        'SELECT DISTINCT zakaznici.* FROM [zakaznici] LEFT JOIN [automaty] USING (id_zakaznik) WHERE hidden=0 AND id_oblast=', $id_oblast,
                        '%if', isset($filtrLike) && $filtrLike!="", ' AND zakaznici.nazev LIKE %s', isset($filtrLike) ? "%" .$filtrLike."%" : '', '%end',
                        '%if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset      
                    )->setRowClass('Zakaznik');
             return $ret;
        }
        
        /**
         * Get Zakaznik entities using LIKE operator. This is how it should be done. Unfortunatly I was using LIKE with Dibi wrong way before.
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @return DibiResult result
         */
        public function getZakaznikyHledani($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $classicWhere = NULL)
        {
            try {
                // prevod parametru WHERE do formy LIKE
                $and = array();
                $and[] = array( '%b', true );
                foreach( $where AS $colName => $colVal )
                {
                     $and[] = array( "$colName LIKE '%$colVal%'");
                }
                $res = dibi::query(
                            'SELECT DISTINCT zakaznici.* FROM [zakaznici] LEFT JOIN [automaty] USING (id_zakaznik) LEFT JOIN [oblasti] USING (id_oblast)
                                WHERE zakaznici.hidden=0 ',
                            '%if', isset($and), 'AND %and', isset($and) ? $and : array(), '%end',
                            '%if', isset($classicWhere), 'AND %and', isset($classicWhere) ? $classicWhere : array(), '%end',
                            '%if', isset($order), 'ORDER BY %by', $order, '%end',
                            '%if', isset($limit), 'LIMIT %i %end', $limit,
                            '%if', isset($offset), 'OFFSET %i %end', $offset      
                        )->setRowClass('Zakaznik');
                return $res;
            }
            catch (DibiException $e)
            {
                Debugger::log("getZakaznikyHledani: " . dibi::$sql);
            }
            return NULL;
        }
        
        /**
         * Get Zakaznik entities who didnt place any order since the date
         * @param date $datum Date since we look for orders
         * @param array $order Order of the output
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @param string $filtrLike unused
         * @return DibiResult result
         */
        public function getZakaznikyHrisniky($datum, $order = NULL, $offset = NULL, $limit = NULL, $filtrLike = NULL)
        {
             $res = dibi::query(
                     'SELECT nazev, id_zakaznik, MAX(datum) as datum FROM [zakaznici] LEFT JOIN objednavky USING (id_zakaznik)
                        WHERE zakaznici.hidden=0 AND NOT    id_zakaznik=0 AND id_zakaznik NOT
                        IN (
                          SELECT id_zakaznik
                          FROM objednavky WHERE datum>"' . $datum . '")
                        AND  id_zakaznik IN (
                          SELECT id_zakaznik
                          FROM automaty)
                          GROUP BY nazev ORDER BY datum ASC ', 
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                     )->setRowClass('Objednavka');
             return $res;
        }
    
        public function getZakaznikyVystupVse($where=NULL, $od = NULL, $do = NULL, $filtr_kategorii = NULL, $filtr_oblasti = NULL)
        {
            try {
                    $res = dibi::query(
                         'SELECT DISTINCT nazev, id_zakaznik FROM [zakaznici] LEFT JOIN objednavky USING (id_zakaznik)
                            WHERE zakaznici.hidden=0 ',
                            '%if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                            '%if', isset($filtr_oblasti), isset($filtr_oblasti) ? "AND (".$filtr_oblasti.")" : "", '%end ',
                            ' AND NOT id_zakaznik=0'
                         )->setRowClass('Zakaznik');
                 return $res;
            }
            catch (DibiException $e)
            {
                Debugger::log("getZakaznikyVystupVse: " . dibi::$sql);
            }
            return NULL;
        }
        
        public function getZakaznikyVystup($where=NULL, $od = NULL, $do = NULL, $platici = true,$filtr_kategorie = NULL, $filtr_oblasti = NULL)
        {
            try {
                if ($platici == false)
                    // neplatici
                    $res = dibi::query(
                         'SELECT DISTINCT nazev, id_zakaznik FROM [zakaznici] LEFT JOIN objednavky USING (id_zakaznik)
                            WHERE zakaznici.hidden=0 ',
                            '%if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                            '%if', isset($filtr_oblasti), isset($filtr_oblasti) ? "AND (".$filtr_oblasti.")" : "", '%end ',
                            ' AND NOT id_zakaznik=0 AND id_zakaznik NOT
                            IN (
                              SELECT id_zakaznik
                              FROM objednavky WHERE datum>="' . $od . '"',
                              'AND datum<="' . $do . '") ORDER BY nazev ASC'
                         )->setRowClass('Zakaznik');
                else
                    // platici
                    $res = dibi::query(
                         'SELECT DISTINCT zakaznici.nazev, id_zakaznik FROM [zakaznici] LEFT JOIN objednavky USING (id_zakaznik) LEFT JOIN zbozi_objednavky USING (id_objednavka) LEFT JOIN zbozi USING (id_zbozi) 
                            WHERE 1=1 ',
                            '%if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                            '%if', isset($filtr_kategorie), isset($filtr_kategorie) ? "AND (".$filtr_kategorie.")" : "", '%end ',
                            '%if', isset($filtr_oblasti), isset($filtr_oblasti) ? "AND (".$filtr_oblasti.")" : "", '%end ',
                            'AND (datum>="' . $od . '"', 'AND datum<="' . $do . '")',
                            '  AND NOT    id_zakaznik=0 AND id_zakaznik   
                            IN (
                              SELECT id_zakaznik
                              FROM objednavky WHERE datum>="' . $od . '"',
                              'AND datum<="' . $do . '") ORDER BY nazev ASC'
                         )->setRowClass('Zakaznik');

                 return $res;
            }
            catch (DibiException $e)
            {
                Debugger::log("getZakaznikyVystup: " . dibi::$sql);
            }
            return NULL;
        }
        
        /**
         * Adds new entity
         * @param Zakaznik new Zakaznik
         * @return type false if fails otherwise id of inserted entity
         */
        public function addZakaznik($zakaznik)
        {
            if (dibi::query("INSERT INTO [zakaznici] ", $zakaznik))
                return dibi::insertId();
            else
                return false;
        }
}

?>
