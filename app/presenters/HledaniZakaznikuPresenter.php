<?php
use Nette\Application\UI\Form;

/**
 * Description of HledaniZakazniku presenter
 *
 * @author mist
 */
class HledaniZakaznikuPresenter extends BasePresenter {

    private $zakazniciModel_var = NULL;
    
    /** @persistent */
    public $filtr_nazev_zakaznika = '';
    /** @persistent */
    public $filtr_bmb = '';
    /** @persistent */
    public $filtr_vyrobni_cislo = '';
    /** @persistent */
    public $filtr_oblast = '';
    /** @persistent */
    public $filtr_umisteni = '';
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    /**
     * action default
     * @param type $id_oblast id of searched area
     */
    public function actionDefault($id_oblast) {
        
    }
    
    /**
     * Form for filtering customers
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentFiltrZakaznici($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->setMethod("GET");
        $form->addText('bmb', 'BMB:');
        $form->addText('umisteni', 'Umístění:');
        $form->addText('vyrobni_cislo', 'Výrobní číslo:');
        $form->addText('oblast', 'Oblast:');
        $form->addText('zakaznik', 'Zákazník:')->setAttribute('autoComplete', "off");
        $form->addSubmit('filtrZakaznici', 'Hledat');
        $form->onSuccess[] = callback($this, 'filtrZakazniky_submit');
        return $form;
    }
    
    /**
     * Button for filtering customers
     * @param type $form for FW
     */
    public function filtrZakazniky_submit($form)
    {
        $this->filtr_bmb = $form['bmb']->getValue();
        $this->filtr_umisteni = $form['umisteni']->getValue();
        $this->filtr_vyrobni_cislo = $form['vyrobni_cislo']->getValue();
        $this->filtr_oblast = $form['oblast']->getValue();
        $this->filtr_nazev_zakaznika = $form['zakaznik']->getValue();

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
        }
    }

    /**
     * render default
     */
    public function renderDefault() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = count($this -> model -> getZakaznikyHledani(NULL,
                array("automaty.bmb" => $this->filtr_bmb, "automaty.umisteni" => $this->filtr_umisteni, 
                    "automaty.vyrobni_cislo" => $this->filtr_vyrobni_cislo, "oblasti.nazev" => $this->filtr_oblast, 
                    "zakaznici.nazev" => $this->filtr_nazev_zakaznika ),
                NULL, NULL));
        $items = $this -> model -> getZakaznikyHledani(array('nazev' => 'ASC'),
                array("automaty.bmb" => $this->filtr_bmb, "automaty.umisteni" => $this->filtr_umisteni, 
                    "automaty.vyrobni_cislo" => $this->filtr_vyrobni_cislo, "oblasti.nazev" => $this->filtr_oblast, 
                    "zakaznici.nazev" => $this->filtr_nazev_zakaznika),
                     $paginator->offset, $paginator->itemsPerPage);

        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }

    /**
     * Singleton for Zakaznici model
     * @return type 
     */
    public function getModel() {
        if(!isset($this->zakazniciModel_var))
            $this->zakazniciModel_var = new ZakazniciModel();

        return $this->zakazniciModel_var;
    }

}