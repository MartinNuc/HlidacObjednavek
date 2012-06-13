<?php
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Diagnostics\Debugger;
/**
 * Description of FixSmlouvyPresenter
 *
 * @author mist
 */
class FixSmlouvyPresenter extends BasePresenter {

    private $objednavkyModel_var = NULL;
    
    /** @persistent */
    public $filtr_od = NULL;
    /** @persistent */
    public $filtr_do = NULL;
    /** @persistent */
    public $filtr_objednavky = '';
    /** @persistent */
    public $filtr_bmb = '';
    /** @persistent */
    public $filtr_zakaznik = '';
    /** @persistent */
    public $filtr_vyrobni_cislo = '';
    
    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {
        
    }
    
    public function handlePrirad($idObjednavky, $idSmlouvy)
    {
        $obj = new Objednavka();
        $obj->id_objednavka = $idObjednavky;
        $obj->id_smlouva = $idSmlouvy;
        $obj->saveWithoutDelete();
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
        }
    }


    /**
     * render seznam
     */
    public function renderDefault() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        // strankovani
        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 40;
        $paginator->itemCount = count($this -> model -> getObjednavkyFixSmlouvy($order = array(
                'datum' => 'DESC', "kod" => "DESC"), array("objednavky.hledani_bmb" => $this->filtr_bmb,
                    "objednavky.hledani_vyrobni_cislo" => $this->filtr_vyrobni_cislo,
                    "objednavky.kod" => $this->filtr_objednavky),
                NULL, NULL, $this->filtr_zakaznik));
        $items = $this -> model -> getObjednavkyFixSmlouvy($order = array(
                'datum' => 'DESC', "kod" => "DESC"), array("objednavky.hledani_bmb" => $this->filtr_bmb,
                    "objednavky.hledani_vyrobni_cislo" => $this->filtr_vyrobni_cislo,
                    "objednavky.kod" => $this->filtr_objednavky),
                $paginator->offset, $paginator->itemsPerPage, $this->filtr_zakaznik);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }
    

    /**
     * Form for filtering orders
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrObjednavek($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('kod', 'Kód:')->setAttribute('autoComplete', "off");
        $form->addText('bmb', 'BMB:')->setAttribute('autoComplete', "off");
        $form->addText('zakaznik', 'Zákazník:')->setAttribute('autoComplete', "off");
        $form->addText('vyrobni_cislo', 'Výrobní číslo automatu:')->setAttribute('autoComplete', "off");
        $form->addSubmit('filtrZbozi', 'Vyhledat');
        $form->onSuccess[] = callback($this, 'filtrObjednavek_submit');
        return $form;
    }
    
    /**
     * Button for filtering orders
     * @param type $form name of form
     */
    public function filtrObjednavek_submit($form)
    {
        $this->filtr_objednavky = $form['kod']->getValue();
        $this->filtr_bmb = $form['bmb']->getValue();
        $this->filtr_zakaznik = $form['zakaznik']->getValue();
        
        $this->filtr_vyrobni_cislo = $form['vyrobni_cislo']->getValue();

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
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
}