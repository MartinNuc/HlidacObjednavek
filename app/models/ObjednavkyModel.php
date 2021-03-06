<?php
use Nette\Diagnostics\Debugger;
/*
 * Model to work with Objednavky entity
 */
class ObjednavkyModel
{
        /**
         * Gets all Kontakt entities from database based on specific criteria
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @param string $filtr filter used for searching by code
         * @return DibiResult result 
         */ 
        public function getObjednavky($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtr = NULL)
        {
            try {
             $ret = dibi::query(
                        'SELECT date_format(objednavky.datum, "%e. %c. %Y") as formatovane_datum, zakaznici.nazev as zakaznik_nazev, zakaznici.hidden as zakaznik_hidden, oblasti.nazev as oblast_nazev, objednavky.* FROM [objednavky] LEFT JOIN [zakaznici] USING (id_zakaznik) LEFT JOIN [oblasti] USING (id_oblast) WHERE 1=1
                         %if', isset($where), ' AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($filtr), ' AND objednavky.kod LIKE "' . $filtr .'%"%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Objednavka');
             return $ret;
            }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavky: " . Dibi::$sql);
            }
             return NULL;
        }
        
/**
         * Search method for Objednavky
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @return DibiResult result 
         */
        public function getObjednavkyFixSmlouvy($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtr_zakaznik = NULL)
        {
            $res = NULL;
            $and = array();
            $and[] = array( '%b', true );
            foreach( $where AS $colName => $colVal )
            {
                 $and[] = array( "$colName LIKE '%$colVal%'");
            }
            try {
            $res = dibi::query(
                        'SELECT date_format(objednavky.datum, "%e. %c. %Y") as formatovane_datum, zakaznici.nazev as zakaznik_nazev, zakaznici.hidden as zakaznik_hidden, smlouvy.poc as novypoc, smlouvy.cislo_smlouvy as novecislosmlouvy, smlouvy.id_smlouva as id_nove_smlouvy, objednavky.*
                            FROM [objednavky] 
                            LEFT JOIN [zakaznici] USING (id_zakaznik)
                            LEFT JOIN [smlouvy] USING (id_zakaznik)
                            WHERE objednavky.id_smlouva=0 AND zakaznici.osobni_zakaznik=0 AND smlouvy.od <= objednavky.datum AND smlouvy.do >= objednavky.datum AND
                         %if', isset($where), ' %and', isset($and) ? $and : array(), '%end',
                        '%if', isset($filtr_zakaznik) && $filtr_zakaznik!="", ' AND zakaznici.nazev LIKE %s', isset($filtr_zakaznik) ? "%" .$filtr_zakaznik."%" : '', '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Objednavka');
            }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavkyOdDo: " . dibi::$sql);
            }
            return $res;
        }
        
        /**
         * Search method for Objednavky
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @return DibiResult result 
         */
        public function getObjednavkyHledani($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtr_zakaznik = NULL)
        {
            $ret = NULL;
            $and = array();
            $and[] = array( '%b', true );
            foreach( $where AS $colName => $colVal )
            {
                 $and[] = array( "$colName LIKE '%$colVal%'");
            }
            try {
            $ret = dibi::query(
                        'SELECT date_format(objednavky.datum, "%e. %c. %Y") as formatovane_datum, zakaznici.nazev as zakaznik_nazev, zakaznici.hidden as zakaznik_hidden, oblasti.nazev as oblast_nazev, objednavky.* FROM [objednavky] LEFT JOIN [zakaznici] USING (id_zakaznik) LEFT JOIN [oblasti] USING (id_oblast) WHERE 
                         %if', isset($where), ' %and', isset($and) ? $and : array(), '%end',
                        '%if', isset($filtr_zakaznik) && $filtr_zakaznik!="", ' AND zakaznici.nazev LIKE %s', isset($filtr_zakaznik) ? "%" .$filtr_zakaznik."%" : '', '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Objednavka');
            }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavkyOdDo: " . dibi::$sql);
            }
            return $ret;
        }

        /**
         * Searches orders by validation date
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @param date $od Date since the order is valid
         * @param date $do Date since the order is invalid
         * @return DibiResult result 
         */
        public function getObjednavkyOdDo($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $od = NULL, $do = NULL)
        {
             try {
                 $ret = dibi::query(
                            'SELECT date_format(objednavky.datum, "%e. %c. %Y") as formatovane_datum, objednavky.* FROM [objednavky] WHERE 1=1 
                             %if', isset($where), ' AND %and', isset($where) ? $where : array(), '%end',
                            '%if', isset($od), ' AND datum>="' . $od . '" %end',
                            '%if', isset($do), ' AND datum<="' . $do . '" %end',
                            '%if', isset($order), 'ORDER BY %by', $order, '%end',
                            '%if', isset($limit), 'LIMIT %i %end', $limit,
                            '%if', isset($offset), 'OFFSET %i %end', $offset
                        )->setRowClass('Objednavka');
                 return $ret;
             }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavkyOdDo: " . dibi::$sql);
            }
            return NULL;
        }
        
        public function getObjednavkyExport($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $od = NULL, $do = NULL, $id_smlouvy = 0)
        {
            try {
             $ret = dibi::query(
                        'SELECT SUM(pocet) 
                            FROM [objednavky] 
                            LEFT JOIN [zakaznici] USING (id_zakaznik) 
                            LEFT JOIN [oblasti] USING (id_oblast)
                            LEFT JOIN [zbozi_objednavky] USING (id_objednavka)
                            LEFT JOIN [zbozi] USING (id_zbozi)
                            WHERE (id_smlouva=', $id_smlouvy, " OR id_smlouva=0) ",
                         '%if', isset($where), ' AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($od), ' AND datum>="' . $od . '" %end',
                        '%if', isset($do), ' AND datum<="' . $do . '" %end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Objednavka');
             return $ret;
            }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavkyExport: " . Dibi::$sql);
            }
             return NULL;
        }
        
        public function getObjednavkyTrasy($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $od = NULL, $do = NULL, $filtr_oblasti = NULL)
        {
            try {
             $ret = dibi::query(
                     'SELECT t.*, GROUP_CONCAT(CONCAT(jmeno, " (",telefon, ")") SEPARATOR ";") as kontakt FROM (',
                        'SELECT DISTINCT date_format(objednavky.datum, "%e. %c. %Y") as formatovane_datum, zakaznici.nazev as zakaznik_nazev, automaty.umisteni, automaty.adresa, zakaznici.hidden as zakaznik_hidden, zakaznici.ico, oblasti.nazev as oblast_nazev, kontakty.jmeno as jmeno, kontakty.telefon as telefon, objednavky.*
                            FROM [objednavky] 
                            LEFT JOIN [zakaznici] USING (id_zakaznik) 
                            LEFT JOIN [oblasti] USING (id_oblast)
                            LEFT JOIN [zbozi_objednavky] USING (id_objednavka)
                            LEFT JOIN [zbozi] USING (id_zbozi)
                            LEFT JOIN [automaty] ON (hledani_vyrobni_cislo=automaty.vyrobni_cislo)
                            LEFT JOIN [automat_kontakt] USING (id_automat)
                            LEFT JOIN [kontakty] USING (id_kontakt)
                            WHERE 1=1
                         %if', isset($where), ' AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($od), ' AND datum>="' . $od . '" %end',
                        '%if', isset($do), ' AND datum<="' . $do . '" %end',
                        '%if', isset($filtr_oblasti), isset($filtr_oblasti) ? "AND (objednavky.".$filtr_oblasti.")" : "", '%end ',          
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset,
                      ') AS t GROUP BY id_objednavka'   
                    )->setRowClass('Objednavka');
             return $ret;
            }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavkyTrasy: " . Dibi::$sql);
            }
             return NULL;
        }
        
        /**
         * Searches orders by validation date
         * @param array $order Order of the output
         * @param array $where WHERE condition
         * @param int $offset used for paging
         * @param int $limit used for paging
         * @param date $od Date since the order is valid
         * @param date $do Date since the order is invalid
         * @return DibiResult result 
         */
        public function getObjednavkyOdDoVystup($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $od = NULL, $do = NULL)
        {
             try {
                 // zpravidla filtrujeme zakaznika a mame jeho konkretni id
                 $ret = dibi::query(
                            'SELECT DISTINCT hledani_bmb as bmb, hledani_vyrobni_cislo  as vyrobni_cislo FROM [objednavky] WHERE 1=1 
                             %if', isset($where), ' AND %and', isset($where) ? $where : array(), '%end',
                            '%if', isset($od), ' AND datum>="' . $od . '" %end',
                            '%if', isset($do), ' AND datum<="' . $do . '" %end',
                                 ' UNION ',
                            'SELECT DISTINCT bmb, vyrobni_cislo FROM [automaty] WHERE 1=1
                                %if', isset($where), ' AND %and', isset($where) ? $where : array(), '%end'
                        )->setRowClass('Objednavka');
                 
                 return $ret;
             }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavkyOdDoVystup: " . dibi::$sql);
            }
            return NULL;
        }
        
        public function getObjednavkyTisk($id_objednavky = NULL)
        {
            try {
                 // zpravidla filtrujeme zakaznika a mame jeho konkretni id
                 $ret = dibi::query(
                            'SELECT DISTINCT zakaznici.nazev as zakaznik_nazev, oblasti.nazev as oblast_nazev, date_format(objednavky.datum, "%e. %c. %Y") as formatovane_datum, objednavky.poznamka as pozn, objednavky.*, zakaznici.* FROM [objednavky_tisk] LEFT JOIN objednavky USING (id_objednavka) LEFT JOIN zakaznici USING (id_zakaznik) LEFT JOIN [oblasti] USING (id_oblast)',
                         '%if', isset($id_objednavky), ' WHERE id_objednavka IN ', $id_objednavky, '%end'
                        )->setRowClass('Objednavka');
                 return $ret;
             }
            catch (DibiException $e)
            {
                Debugger::log("getObjednavkyTisk: " . dibi::$sql);
            }
            return NULL;
        }
        
        public function deleteTiskObjednavky($inrange)
        {
            try {
                $ret = dibi::query('DELETE FROM [objednavky_tisk] WHERE id_objednavka ', $inrange);
            }
            catch (DibiException $e)
            {
                Debugger::log("deleteTiskObjednavky: " . dibi::$sql);
            }
            return $ret;
        }
        
        public function addObjednavkaPrint($id_objednavka)
        {
            if (dibi::query("INSERT INTO [objednavky_tisk] VALUES (", $id_objednavka,')'))
                return true;
            else
                return false;
        }
        
        /**
         * Adds new entity
         * @param Objednavka new Objednavka
         * @return type false if fails otherwise id of inserted entity
         */
        public function addObjednavka($objednavka)
        {
            if (dibi::query("INSERT INTO [objednavky] ", $objednavka))
                return dibi::insertId();
            else
                return false;
        }
}

?>
