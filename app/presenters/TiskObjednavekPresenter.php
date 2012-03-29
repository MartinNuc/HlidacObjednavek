<?php
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Diagnostics\Debugger;
/**
 * Description of TiskObjednavek
 *
 * @author mist
 */
class TiskObjednavekPresenter extends BasePresenter {

    /** @persistent */
    public $list_objednavek = array();
    /** @persistent */
    public $smazat = NULL;
    private $objednavkyModel_var;
    private $kontaktyModel_var;
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    public function createComponentTiskObjednavky($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');

        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->addButton('back', 'Zpět')->getControlPrototype()->class("back");
        return $form;
    }

    public function actionDefault($id_objednavka) {
        
    }

    public function renderDefault($id_objednavka) {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');
        
        $objednavka = new Objednavka();
        $objednavka->id_objednavka = $id_objednavka;
        $objednavka->fetch();
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = $objednavka->id_zakaznik;
        $zakaznik->fetch();
        $this->template->zakaznik = $zakaznik;
        
        $this->template->kontakty = array();
        $this->template->kontakty = $this->kontaktyModel->getKontaktyInContext(NULL, array("vyrobni_cislo" => $objednavka->hledani_vyrobni_cislo));

        $this->template->zbozi = $objednavka->getZbozi();
        $this->template->objednavka = $objednavka;
    }
    
    /******* SEZNAM *******/
    
    public function tiskObjednavky_submit($button)
    {
        $form = $button->form;
        $objednavky = $this->model->getObjednavkyTisk();
        foreach ($objednavky as $obj)
            if ($form['obj_' . $obj->id_objednavka]->getValue() == true)
                $this->list_objednavek[] = $obj->id_objednavka;

        $this->redirect('TiskObjednavek:tiskvice');
    }
    
    public function tiskObjednavkyNajednou_submit($button)
    {
        $form = $button->form;
        $objednavky = $this->model->getObjednavkyTisk();
        foreach ($objednavky as $obj)
            if ($form['obj_' . $obj->id_objednavka]->getValue() == true)
                $this->list_objednavek[] = $obj->id_objednavka;

        $this->redirect('TiskObjednavek:tisknajednou');
    }
    
    public function odstranitObjednavky_submit($button)
    {
        $form = $button->form;
        $objednavky = $this->model->getObjednavkyTisk();
        $i=0;
        $where = "";
        foreach ($objednavky as $obj)
        {
            if ($form['obj_' . $obj->id_objednavka]->getValue() == true)
            {
                if ($i == 0)
                {
                    $where = " IN (" . $obj->id_objednavka;
                    $i++;
                }
                else
                    $where .= "," . $obj->id_objednavka;
            }

        }
        if ($where != "")
        {
            $where .= ")";
            $this->model->deleteTiskObjednavky($where);
        }
        else
            return;
        
        $this->redirect('TiskObjednavek:seznam');
    }

    public function createComponentTiskObjednavek($name)
    {
        $form = new Form($this, $name);
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_kategorie'));
        $objednavky = $this->model->getObjednavkyTisk();
        foreach ($objednavky as $obj)
            $form->addCheckbox('obj_' . $obj->id_objednavka, $obj->formatovane_datum . " (" . $obj->nazev . ")")->setDefaultValue(false)->getControlPrototype()->class('chkbox');

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addSubmit('tiskObjednavkyNajednou', 'Tisknout vybrané najednou')->onClick[] = callback($this, 'tiskObjednavkyNajednou_submit');
        $form->addSubmit('tiskObjednavky', 'Tisknout vybrané postupně')->onClick[] = callback($this, 'tiskObjednavky_submit');
        $form->addSubmit('zahoditObjednavky', 'Zahodit vybrané')->onClick[] = callback($this, 'odstranitObjednavky_submit');
        //$form->onSuccess[] = callback($this, 'tiskObjednavky_submit');
        return $form;
    }
    
    public function renderSeznam() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');
        
        $objednavky = $this->model->getObjednavkyTisk();
        $this->template->objednavky = $objednavky;
        
    }
    
    /********* TISK VICE *******/

    public function tiskDalsiObjednavky_submit($form)
    {
        if (isset($this->smazat) && $this->smazat != NULL)
            $this->model->deleteTiskObjednavky(" IN (" . $this->smazat . ")");
        $this->redirect('TiskObjednavek:tiskvice');
    }

    public function createComponentTiskDalsiObjednavek($name)
    {
        $form = new Form($this, $name);
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->addSubmit('tiskObjednavky', 'Přejít na další objednávku');
        $form->onSuccess[] = callback($this, 'tiskDalsiObjednavky_submit');
        return $form;
    }
    
    public function actionTiskvice() {
        
    }

    public function renderTiskvice() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');
        
        if (count($this->list_objednavek) == 0)
        {
            $this->flashMessage ("Vybrané objednávky byly vytištěny.");
            $this->redirect('TiskObjednavek:seznam');
        }
        
        $objednavka = new Objednavka();
        $objednavka->id_objednavka = array_pop($this->list_objednavek);
        $this->smazat = $objednavka->id_objednavka;
        $objednavka->fetch();

        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = $objednavka->id_zakaznik;
        $zakaznik->fetch();
        
        $this->template->kontakty = array();
        $this->template->kontakty = $this->kontaktyModel->getKontaktyInContext(NULL, array("vyrobni_cislo" => $objednavka->hledani_vyrobni_cislo));

        $this->template->zakaznik = $zakaznik;
        $this->template->objednavka = $objednavka;
        $this->template->zbozi = $objednavka->getZbozi();
    }
    
    public function renderTisknajednou() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');
        
        if (count($this->list_objednavek) == 0)
        {
            $this->flashMessage ("Vybrané objednávky byly vytištěny.");
            $this->redirect('TiskObjednavek:seznam');
        }
        $temp = "";
        foreach ($this->list_objednavek as $obj)
            $temp=$temp . $obj . " ";
        $temp = trim($temp);
        $temp = '(' . str_replace(' ', ',', $temp) . ')';
        $this->template->objednavky = $this->model->getObjednavkyTisk($temp);
        
        $zakaznik = new Zakaznik();
        $this->template->zakaznik = array();
        $this->template->zbozi = array();
        $this->template->kontakty = array();
        foreach ($this->template->objednavky as $obj)
        {
            $zakaznik->id_zakaznik = $obj->id_zakaznik;
            $zakaznik->fetch();
            $this->template->zakaznik[$obj->id_objednavka] = $zakaznik->ico;
            $this->template->kontakty[$obj->id_objednavka] = $this->kontaktyModel->getKontaktyInContext(NULL, array("vyrobni_cislo" => $obj->hledani_vyrobni_cislo));
            
            $this->template->zbozi[$obj->id_objednavka] = $obj->getZbozi();
        }
    }
    
    /**
     * Singleton ObjednavkyModel
     * @return type 
     */
    public function getModel() {
        if(!isset($this->objednavkyModel_var))
            $this->objednavkyModel_var = new ObjednavkyModel();

        return $this->objednavkyModel_var;
    }

    public function getKontaktyModel() {
        if(!isset($this->kontaktyModel_var))
            $this->kontaktyModel_var = new KontaktyModel();

        return $this->kontaktyModel_var;
    }
}