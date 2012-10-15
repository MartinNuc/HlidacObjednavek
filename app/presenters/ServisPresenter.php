<?php
use Nette\Application\UI\Form;
/**
 * Description of ServisPresenter
 *
 * @author mist
 */
class ServisPresenter extends BasePresenter {

    private $automatyModel_var = NULL;

    /** @persistent */
    public $filtr_nazev = '';
    /** @persistent */
    public $filtr_bmb = '';
    /** @persistent */
    public $filtr_vyrobni_cislo = '';
    /** @persistent */
    public $filtr_layout = '';
    
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    public function createComponentFiltrAutomaty($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->setMethod("GET");
        $form->addText('nazev', 'Název automatu:')->setAttribute('autoComplete', "off");;
        $form->addText('bmb', 'BMB:')->setAttribute('autoComplete', "off");;
        $form->addText('vyrobni_cislo', 'Výrobní číslo:')->setAttribute('autoComplete', "off");;
        $form->addText('layout', 'Layout:')->setAttribute('autoComplete', "off");;
        $form->addSubmit('filtrAutomaty', 'Hledat');
        $form->onSuccess[] = callback($this, 'filtrAutomaty_submit');
        return $form;
    }
    
    public function filtrAutomaty_submit($form)
    {
        $this->filtr_bmb = $form['bmb']->getValue();
        $this->filtr_nazev = $form['nazev']->getValue();
        $this->filtr_vyrobni_cislo = $form['vyrobni_cislo']->getValue();
        $this->filtr_layout = $form['layout']->getValue();

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
        }
    }

    public function actionDefault() {
        
    }

    public function renderDefault() {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');

        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        
        if ($this->getUser()->isInRole('host'))
        {
            $where = array("automaty.bmb" => $this->filtr_bmb, "automaty.layout" => $this->filtr_layout,
                        "automaty.vyrobni_cislo" => $this->filtr_vyrobni_cislo, "automaty.nazev" => $this->filtr_nazev,
                        "automaty.osobni" => 0, "zakaznici.osobni_zakaznik" => 0);
        }
        else
        {
            $where = array("automaty.bmb" => $this->filtr_bmb, "automaty.layout" => $this->filtr_layout,
                        "automaty.vyrobni_cislo" => $this->filtr_vyrobni_cislo, "automaty.nazev" => $this->filtr_nazev);       
        }
        

        
        $paginator->itemCount = count($this -> model -> getAutomatyHledani(NULL, $where,NULL, NULL));
        $items = $this -> model -> getAutomatyHledani(array("id_oblast" => "DSC", "zakaznik_nazev" => "ASC"),
                $where,$paginator->offset, $paginator->itemsPerPage);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }
    
    public function getModel() {
        if(!isset($this->automatyModel_var))
            $this->automatyModel_var = new AutomatyModel();

        return $this->automatyModel_var;
    }

}