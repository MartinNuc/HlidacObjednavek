<?php
use Nette\Application\UI\Form;
use Nette\Utils\Html;

/**
 * Description of ServisPresenter
 *
 * @author mist
 */
class ServisPresenter extends BasePresenter {

    private $automatyModel_var = NULL;
    private $oblastiModel_var = NULL;
    private $opravyModel_var = NULL;
    private $skupinyModel_var = NULL;

    private $filtr_od = NULL;
    private $filtr_do = NULL;

    private $oblasti = array();
    private $skupiny = array();

    
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
    
    public function createComponentFiltrOblasti($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        /*$renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div');*/

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_oblasti'));
        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                $form->addCheckbox('oblast_' . $oblast->id_oblast, $oblast->nazev)->setDefaultValue(false);

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_datum'));
        $form->addDatePicker('od', "Od")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y',mktime(0,0,0,date('m'),date('d'),date('y'))));
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
        $form->addDatePicker('doo', "Do")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y'));
        $this->filtr_do = Date('j-n-Y');
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addSubmit('filtrZakaznici', 'Nastavit kritéria');
        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->onSuccess[] = callback($this, 'filtrOblasti_submit');
        return $form;
    }
    
    public function filtrOblasti_submit($form)
    {
        $datum = $form['od']->getValue();
        if ($datum == "")
            $this->filtr_od = NULL;
        else
            $this->filtr_od = $datum->format("Y-n-j");
        
        $datum = $form['doo']->getValue();
        if ($datum == "")
            $this->filtr_do = NULL;
        else
            $this->filtr_do = $datum->format("Y-n-j");

        $oblasti = $this->oblastiModel->getOblasti();
        foreach ($oblasti as $oblast)
            if ($oblast->id_oblast != 0)
                if ($form['oblast_' . $oblast->id_oblast]->getValue() == true)
                    $this->oblasti[] = $oblast->id_oblast;

        if (!$this->isAjax())
            $this->redirect('Servis:oblasti');
        else {
            $this->invalidateControl('strankyLong');
        }
    }

    public function createComponentFiltrSkupiny($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        /*$renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div');*/

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_oblasti'));
        $skupiny = $this->skupinyModel->getSkupiny();
        foreach ($skupiny as $skupina)
            $form->addCheckbox('skupina_' . $skupina->id_skupina, $skupina->nazev)->setDefaultValue(false);

        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_datum'));
        $form->addDatePicker('od', "Od")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y',mktime(0,0,0,date('m'),date('d'),date('y'))));
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
        $form->addDatePicker('doo', "Do")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y'));
        $this->filtr_do = Date('j-n-Y');
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('tisk_tlacitka'));
        $form->addSubmit('filtrZakaznici', 'Nastavit kritéria');
        $form->addButton('print', 'Tisk')->getControlPrototype()->class("print");
        $form->onSuccess[] = callback($this, 'filtrSkupiny_submit');
        return $form;
    }
    
    public function filtrSkupiny_submit($form)
    {
        $datum = $form['od']->getValue();
        if ($datum == "")
            $this->filtr_od = NULL;
        else
            $this->filtr_od = $datum->format("Y-n-j");
        
        $datum = $form['doo']->getValue();
        if ($datum == "")
            $this->filtr_do = NULL;
        else
            $this->filtr_do = $datum->format("Y-n-j");

        $skupiny = $this->skupinyModel->getSkupiny();
        foreach ($skupiny as $skupina)
            if ($form['skupina_' . $skupina->id_skupina]->getValue() == true)
                $this->skupiny[] = $skupina->id_skupina;

        if (!$this->isAjax())
            $this->redirect('Servis:skupiny');
        else {
            $this->invalidateControl('strankyLong');
        }
    }
    
    public function actionVystupy() {
        
    }

    public function renderVystupy() {
    }
    
    public function actionOblasti() {
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
        $this->filtr_do = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
    }

    public function renderOblasti() {
        $filtr_oblasti = "";
        foreach ($this->oblasti as $oblast)
        {
            if ($this->oblasti[0] == $oblast)
                $filtr_oblasti .= "id_oblast IN (" . $oblast;
            else
                $filtr_oblasti .= "," . $oblast;
        }
        if ($filtr_oblasti != "")
            $filtr_oblasti .= ")";
        else
            $filtr_oblasti = "id_oblast=-1";
        
        $this->template->items = $this->opravyModel->getOpravyContext(array("datum" => "DESC"),
                array(array("datum <= %d", $this->filtr_do), array("datum >= %d", $this->filtr_od)),
                NULL, NULL, $filtr_oblasti);

    }
    
    public function actionSkupiny() {
        $this->filtr_od = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
        $this->filtr_do = Date('j-n-Y',mktime(0,0,0,date('m'),date('d'),date('y')));
    }

    public function renderSkupiny() {
        $filtr_skupiny = "";
        foreach ($this->skupiny as $skupina)
        {
            if ($this->skupiny[0] == $skupina)
                $filtr_skupiny .= "id_skupina IN (" . $skupina;
            else
                $filtr_skupiny .= "," . $skupina;
        }
        if ($filtr_skupiny != "")
            $filtr_skupiny .= ")";
        else
            $filtr_skupiny = "id_skupina=-1";
        
        $this->template->items = $this->opravyModel->getOpravyContext(array("datum" => "DESC"),
                array(array("datum <= %d", $this->filtr_do), array("datum >= %d", $this->filtr_od)),
                NULL, NULL, $filtr_skupiny);

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
    
    public function getOpravyModel() {
        if(!isset($this->opravyModel_var))
            $this->opravyModel_var = new OpravyModel();

        return $this->opravyModel_var;
    }   

    public function getOblastiModel() {
        if(!isset($this->oblastiModel_var))
            $this->oblastiModel_var = new OblastiModel();

        return $this->oblastiModel_var;
    }
    
    public function getSkupinyModel() {
        if(!isset($this->skupinyModel_var))
            $this->skupinyModel_var = new SkupinyModel();

        return $this->skupinyModel_var;
    }

}