<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SessionPanel
 *
 * @author mist
 */
class SessionPanel implements \Nette\Diagnostics\IBarPanel {
        private $sess;

        public function __construct(\Nette\Http\Session $sess) {
                $this->sess = $sess;
        }

        function getTab() {
                return $this->sess->getIterator()->count() . ' sessions';
        }

        function getPanel() {
                $ret = array();
                foreach($this->sess->getIterator() as $ns) $ret[$ns] = iterator_to_array($this->sess->getSection($ns));
                return \Nette\Diagnostics\Debugger::dump($ret, true);
        }
}

?>
