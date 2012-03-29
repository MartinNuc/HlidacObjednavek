<?php
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
/**
 * Description of Hlidac
 *
 * @author mist
 */
class HlidacPresenter extends BasePresenter {

    private $zakazniciModel_var = NULL;
    
    /** @persistent */
    public $filtr_hlidac = "1";
    
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    /**
     * Form for filtering orders
     * @param type $name form name
     * @return Form for FW
     */
    public function createComponentFiltrObjednavky($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        
        $form->addText('filtr', 'Počet prohledávaných měsíců: ')->setAttribute('autoComplete', "off")->setDefaultValue("1");
        
        $form->addSubmit('filtrObjednavky', 'Zobrazit');
        $form->onSuccess[] = callback($this, 'filtrObjednavky_submit');
        return $form;
    }
    
    /**
     * Button for filtering form
     * @param type $form for FW
     */
    public function filtrObjednavky_submit($form)
    {
        $this->filtr_hlidac = $form['filtr']->getValue();

        if (!$this->isAjax())
            $this->redirect('Hlidac:default');
        else {
            $this->invalidateControl('seznam');
        }
    }

    /**
     * action default
     */
    public function actionDefault() {
        
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
        /*if (isset($this->filtr_hlidac) == false)
                $this->filtr_hlidac = "1";*/

        //$date = date("Y-m-j");
        $newdate = strtotime ( '-' . $this->filtr_hlidac . ' months' ) ;
        $newdate = date ( 'Y-m-j' , $newdate );
        
        $paginator->itemCount = count($this -> model -> getZakaznikyHrisniky($newdate, NULL,NULL,NULL));
        
        $items = $this -> model -> getZakaznikyHrisniky($newdate, NULL, $paginator->offset, $paginator->itemsPerPage);
        $predelane = array();
        foreach ($items as $item)
        {
            $temp = new DibiRow(array());
            if (isset($item->datum) && $item->datum != NULL)
            {
                $datum = new DateTime($item->datum);
                $temp->datum = $datum->format("j. n. Y");
            }
             else
                 $temp->datum = "--";

            $temp->nazev = $item->nazev;
            $temp->id_zakaznik = $item->id_zakaznik;
            $predelane[] = $temp;
        }
            
        $this->template->items = $predelane ;
        if ($this->isAjax())
            $this->invalidateControl('seznam');
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