<?php
use Nette\Diagnostics\Debugger;
/*
 * Kontakt model to work with Kontakt entities
 */
class KontaktyModel
{
    /**
     * Gets all Kontakt entities from database based on specific criteria
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @return DibiResult result 
     */        
      public function getKontakty($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $nazevLike = NULL)
        {
             return dibi::query(
                        'SELECT * FROM [kontakty] WHERE 1=1
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($nazevLike) && $nazevLike!="", ' AND jmeno LIKE %s', isset($nazevLike) ? "%" .$nazevLike."%" : '', '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Kontakt');
        }
        
    /**
     * Gets all Kontakt entities from database based on specific criteria with LIKE operator
     * @param array $order Order of the output
     * @param array $where WHERE condition
     * @param int $offset used for paging
     * @param int $limit used for paging
     * @param string $nazevLike Filtr by nazev using operator LIKE
     * @return DibiResult result 
     */  
        public function getKontaktyInContext($order = NULL, $where = NULL, $offset = NULL, $limit = NULL, $nazevLike = NULL)
        {
             $ret = dibi::query(
                        'SELECT * FROM [automat_kontakt] LEFT JOIN [automaty] USING (id_automat) LEFT JOIN [kontakty] USING (id_kontakt) WHERE 1=1
                         %if', isset($where), 'AND %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($nazevLike) && $nazevLike!="", ' AND automaty.nazev LIKE %s', isset($nazevLike) ? "%" .$nazevLike."%" : '', '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
                    )->setRowClass('Kontakt');
             return $ret;
        }
           
        
        public function getEmaily()
        {
             return dibi::query(
                        'SELECT email FROM [kontakty] UNION SELECT email from [zakaznici]'
                    )->setRowClass('Kontakt');     
        }
        /**
         * Adds new entity
         * @param Kontakt new Kontakt
         * @return type false if fails otherwise id of inserted entity
         */
        public function addKontakt($kontakt)
        {
            if (dibi::query("INSERT INTO [kontakty] ", $kontakt))
                return dibi::insertId();
            else
                return false;
        }
}

?>
