<?php
use Nette\Diagnostics\Debugger;
/*
 * Model to work with automats
 */
class AutomatyModel
{
    /**
     * Gets all automats from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @param string $filtr used for filtering results using LIKE operator
     * @return DibiResult result
     */
        public function getAutomaty($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $filtr = NULL)
        {
                        try{
                 $ret = dibi::query(
                        'SELECT technici.jmeno as technik_jmeno, technici.prijmeni as technik_prijmeni,
                            obchodni_zastupci.jmeno as oz_jmeno, obchodni_zastupci.telefon as oz_telefon,
                            zakaznici.nazev as zakaznik_nazev, zakaznici.hidden as zakaznik_hidden, zakaznici.adresa as zakaznik_adresa, zakaznici.*, automaty.*,
                            oblasti.nazev as oblast_nazev
                            FROM [automaty] LEFT JOIN [zakaznici] USING (id_zakaznik)
                            LEFT JOIN [oblasti] USING (id_oblast)
                            LEFT JOIN [obchodni_zastupci] USING (id_obchodni_zastupce)
                            LEFT JOIN [technici] USING (id_oblast)',
                           ' WHERE 1=1 ',
                        '%if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($filtr) && $filtr!="", ' AND (automaty.nazev LIKE %s', isset($filtr) ? "%" . $filtr ."%" : '',
                        ' OR ', ' automaty.umisteni LIKE "%' . $filtr .'%"',
                        ' OR ', ' automaty.bmb LIKE "%' . $filtr .'%"',
                        ' OR ', ' automaty.vyrobni_cislo LIKE "%' . $filtr .'%"',
                        ' OR ', ' oblasti.nazev LIKE "%' . $filtr .'%"',
                        ' OR ', ' zakaznici.nazev LIKE "%' . $filtr .'%"',
                        ') %end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Automat');
                return $ret;
             }
            catch (DibiDriverException $e)
            {
                Debugger::log("getAutomaty: " . dibi::$sql);
            }
             return array();
        }
        
        public function getAutomatyVystup($order = NULL, $where = NULL, $filtr_oblasti = NULL)
        {
             try{
                 $ret = dibi::query(
                            'SELECT automaty.adresa as automat_adresa, zakaznici.nazev as zakaznik_nazev, DATE_FORMAT(smlouvy.do,"%e.%c.%Y") as platnost_do, automaty.*, oblasti.*, smlouvy.*, zakaznici.* 
                                FROM [automaty] LEFT JOIN [zakaznici] USING (id_zakaznik)
                                LEFT JOIN [oblasti] USING (id_oblast)',
                                'LEFT JOIN [smlouvy] USING (id_zakaznik)',
                               ' WHERE 1=1 ',
                            '%if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                            '%if', isset($filtr_oblasti), isset($filtr_oblasti) ? "AND (".$filtr_oblasti.")" : "", '%end ',
                            '%if', isset($order), 'ORDER BY %by', $order, '%end'
                        )->setRowClass('Automat');
                 return $ret;
            }
            catch (DibiDriverException $e)
            {
                Debugger::log("getAutomatyVystup: " . dibi::$sql);
            }
             return array();
        }
        
        public function getFirstKontakt($id_automat = NULL)
        {
            $res = dibi::query(
                     'SELECT id_automat, id_kontakt, jmeno, email
                         FROM [automat_kontakt] LEFT JOIN [kontakty] USING (id_kontakt)
                        WHERE 1=1 ',
                          '%if', isset($id_automat), 'AND %and', isset($id_automat) ? $id_automat : array(), '%end',
                     "ORDER BY id_automat"
                     )->setRowClass('Zbozi');
            return $res; 
        }
        
        /**
         * Inserts new automat into database
         * @param type $automat Automat entity we want to save
         * @return type false if fails otherwise id of inserted automat
         */
        public function addAutomat($automat)
        {
            if (dibi::query("INSERT INTO [automaty] ", $automat))
                return dibi::insertId();
            else
                return false;
        }
}

?>
