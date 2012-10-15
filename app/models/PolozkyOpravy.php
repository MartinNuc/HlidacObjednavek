<?php

use Nette\Diagnostics\Debugger;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PolozkyOpravy
 *
 * @author mist
 */
class PolozkyOpravy  extends Nette\Object 
{
        private $opravy;

        public function __construct(Nette\Http\Session $session)
        {
                $this->opravy = $session->getSection(__CLASS__);
        }

        public function add(PolozkaOpravy $item)
        {
            if (isset($this->opravy) == false)
                $this->opravy = array();
            $item->id = Nette\Utils\Strings::random(15);
            $this->opravy[$item->id] = new \ArrayObject(array("pocet" => $item->pocet, "id" => $item->id, "cena" => $item->cena, "id_skupina" => $item->id_skupina, "popis" => $item->popis, "placene_zakaznikem" => $item->placene_zakaznikem));
        }
 
        public function remove($id)
        {
            $this->opravy[$id] = null;
            unset($this->opravy[$id]);
        }
        
        public function clean()
        {
            foreach ($this->opravy AS $key => $val)
            {
                    $this->opravy->$key = null;
                    unset($this->opravy->$key);
            }
        }

        public function getItems()
        {
            if (count($this->opravy) > 0)
                return $this->opravy->getIterator()->getArrayCopy();
            else
                return array();
        }
}

?>
