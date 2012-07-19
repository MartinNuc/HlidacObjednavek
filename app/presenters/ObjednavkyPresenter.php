<?php
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Diagnostics\Debugger;
/**
 * Description of ObjednavkyPresenter
 *
 * @author mist
 */
class ObjednavkyPresenter extends BasePresenter {
    private $zakazniciModel_var = NULL;
    private $smlouvyModel_var = NULL;
    private $zboziModel_var = NULL;
    private $automatyModel_var = NULL;
    private $objednavkyModel_var = NULL;
    private $kontaktyModel_var = NULL;
    private $kategorieModel_var = NULL;
    
    private $id_zakaznik = NULL;
    private $id_automat = NULL;
    private $id_objednavka = NULL;
    
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
    
    private $zisk = 0;
    private $cena_bez_dph = 0;
    private $cena_s_dph = 0;
    private $body = 0;
    
    private $aktualni_oblast = NULL;

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    /**
     * Form for creating new order
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentNovaObjednavka($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $renderer = $form->getRenderer();
        
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div')->class('prvek');
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('obj_datum'));
        $form->addDatePicker('datum', "Datum: ")
            ->addRule(Form::VALID, 'Zadané datum není platné.')
            ->setDefaultValue(date("d. m. Y"));
            
        
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = $this->id_zakaznik;
        $zakaznik->fetch();
        $res = $zakaznik->getZboziZakaznika()->fetchPairs('id_zbozi', 've_smlouve');
        
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "ASC"), array('id_kategorie' => $kat->id_kategorie));
            $form->addGroup($kat->nazev)->setOption('container', Html::el('fieldset')->class('obj_group'));
            foreach ($zbozi as $zboz)
            {
                // pokud zakaznik ma zbozi ve smlouve, tak ho vykreslime s class red
                if (isset($res[$zboz->id_zbozi])==true)
                {
                    if ($res[$zboz->id_zbozi]==1)
                        $form->addText('nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi, $zboz->zkratka)->setAttribute('autoComplete', "off")->getLabelPrototype()->class('obj_label red');
                    else
                        $form->addText('nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi, $zboz->zkratka)->setAttribute('autoComplete', "off")->getLabelPrototype()->class('obj_label yellow');
                }
                else
                    $form->addText('nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi, $zboz->zkratka)->setAttribute('autoComplete', "off")->getLabelPrototype()->class('obj_label');
                $form['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->getControlPrototype()->class('obj_zbozi');
            }
        }
        
        $form->setCurrentGroup(NULL);
        $form->addTextArea('poznamka', 'Poznámka:',45,2)->getLabelPrototype()->class('objednavka_poznamka');
        
        // POC
        $pole = array();
        $pref = $this->smlouvyModel->getSmlouvy(NULL, array("id_zakaznik" => $zakaznik->id_zakaznik, "preferovany_poc" => 1))->fetchSingle();
        foreach ($this->smlouvyModel->getSmlouvy(NULL, array("id_zakaznik" => $zakaznik->id_zakaznik)) as $key => $value)
            $pole[$value->id_smlouva]=$value->poc;
        $form->addSelect('id_smlouva', 'POC:', $pole)->setDefaultValue($pref);
        
        $recalc = $form->addSubmit('prepocitat', 'Přepočítat cenu');        
        $recalc->onClick[] = callback($this, 'prepocitatCenu_submit');
        $recalc->getControlPrototype()->class('hidden');
        $form->addSubmit('novaObjednavka', 'Zadat objednávku')->onClick[] = callback($this, 'novaObjednavka_submit');
        $form->addSubmit('novaObjednavkaPrint', 'Zadat objednávku a vytisknout')->onClick[] = callback($this, 'novaObjednavkaPrint_submit');
        
        $form->addHidden('id');
        $form->addHidden('id_automat');
        
        //$form->onSuccess[] = array($this, '');
        return $form;
    }
        
    /**
     * AJAX request for recalculating price of order
     * @param type $button Button pressed
     */
    public function prepocitatCenu_submit($button)
    {
        $kategorie = $this->kategorieModel->getKategorie();
        $form = $this["novaObjednavka"];
        $this->cena_s_dph = 0;
        $this->cena_bez_dph = 0;
        $this->body = 0;
        $this->zisk = 0;
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(NULL, array('id_kategorie' => $kat->id_kategorie));
            foreach ($zbozi as $zboz)
            {
                $val = $form['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->getValue(); 
                if (is_numeric($val))
                {
                    $this->cena_bez_dph = $this->cena_bez_dph + $val * $zboz->prodejni_cena;
                    $this->cena_s_dph = $this->cena_s_dph + round($val* ($zboz->prodejni_cena + $zboz->prodejni_cena /100 * $zboz->dph), 1);
                    $this->zisk = $this->zisk + $val * ($zboz->prodejni_cena - $zboz->nakupni_cena);
                    $this->body = $this->body + $val * $zboz->body;
                }
            }
        }
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('cena');
        }
    }
    
    /**
     * AJAX request for recalculating price of order in editting form
     * @param type $button Button pressed
     */
    public function prepocitatCenuEdit_submit($button)
    {
        $kategorie = $this->kategorieModel->getKategorie();
        $form = $this["editObjednavka"];
        $this->cena_s_dph = 0;
        $this->cena_bez_dph = 0;
        $this->body = 0;
        $this->zisk = 0;
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(NULL, array('id_kategorie' => $kat->id_kategorie));
            foreach ($zbozi as $zboz)
            {
                $val = $form['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->getValue(); 
                if (is_numeric($val))
                {
                    $this->cena_bez_dph = $this->cena_bez_dph + $val * $zboz->prodejni_cena;
                    $this->cena_s_dph = $this->cena_s_dph + round($val* ($zboz->prodejni_cena + $zboz->prodejni_cena /100 * $zboz->dph), 1);
                    $this->zisk = $this->zisk + $val * ($zboz->prodejni_cena - $zboz->nakupni_cena);
                    $this->body = $this->body + $val * $zboz->body;
                }
            }
        }
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('cena');
        }
    }

    /**
     * Adds leading zeroes depending on lenght we need
     * @param type $num number we wanted to add zeroes to
     * @param type $numDigits number od digits we need
     * @return string returns number with leading zeros
     */
    private function leadingZeros($num,$numDigits) {
       return sprintf("%0".$numDigits."d",$num);
    }
    
    /**
     * Button for creating new order
     * @param type $button button pressed
     * @return type Returns if something fails
     */
    public function novaObjednavka_submit($button)
    {
        $form = $this["novaObjednavka"];
        $objednavka = new Objednavka();

        $datum = $form['datum']->getValue();
        
        $automat = new Automat();
        $automat->id_automat = $form['id_automat']->getValue();
        $automat->fetch();
        $objednavka->hledani_bmb = $automat->bmb;
        $objednavka->hledani_vyrobni_cislo = $automat->vyrobni_cislo;
        $objednavka->id_oblast = $automat->id_oblast;
        
        $objednavka->id_smlouva = $form['id_smlouva']->getValue();
        
        $objednavka->datum = $datum->format('Y-n-j');
        $objednavka->id_zakaznik = $form['id']->getValue();
        $posledni_kod = $objednavka->getPosledniKod($this->leadingZeros($objednavka->id_oblast,2) . $datum->format('m') . $datum->format('y'));
        $objednavka->kod = $this->leadingZeros($objednavka->id_oblast,2) . $datum->format('m') . $datum->format('y') . $this->leadingZeros($posledni_kod,4);
        
        $objednavka->poznamka = $form['poznamka']->getValue();
        
        $id_objednavka = $this->model->addObjednavka($objednavka);
        
        if ($id_objednavka == false)
        {
            $this->flashMessage('Objednávku se nepodařilo uložit.','error');
            return;
        }
        
        $objednavka->id_objednavka = $id_objednavka;
        $this->cena_s_dph = 0;
        $this->cena_bez_dph = 0;
        $this->body = 0;
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "ASC"), array('id_kategorie' => $kat->id_kategorie));
            foreach ($zbozi as $zboz)
            {
                $val = $form['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->getValue(); 
                if (is_numeric($val))
                {
                    $this->body = $this->body + $val * $zboz->body;                    
                    $this->cena_bez_dph = $this->cena_bez_dph + $val * $zboz->prodejni_cena;
                    $this->cena_s_dph = $this->cena_s_dph + round($val* ($zboz->prodejni_cena + $zboz->prodejni_cena /100 * $zboz->dph), 1);
                    $objednavka->pridatZboziDoObjednavky($zboz->id_zbozi, $val);
                }
            }
        }
        
        $objednavka->setCena($this->cena_bez_dph, $this->cena_s_dph, $this->body);
        $this->model->addObjednavkaPrint($objednavka->id_objednavka);
        $this->redirect('this');
    }
    
    /**
     * Button for creating new order
     * @param type $button button pressed
     * @return type Returns if something fails
     */
    public function novaObjednavkaPrint_submit($button)
    {
        $form = $this["novaObjednavka"];
        $objednavka = new Objednavka();

        $datum = $form['datum']->getValue();
        
        $automat = new Automat();
        $automat->id_automat = $form['id_automat']->getValue();
        $automat->fetch();
        $objednavka->hledani_bmb = $automat->bmb;
        $objednavka->hledani_vyrobni_cislo = $automat->vyrobni_cislo;
        $objednavka->id_oblast = $automat->id_oblast;
        
        
        $objednavka->datum = $datum->format('Y-n-j');
        $objednavka->id_zakaznik = $form['id']->getValue();
        $posledni_kod = $objednavka->getPosledniKod($this->leadingZeros($objednavka->id_oblast,2) . $datum->format('m') . $datum->format('y'));
        $objednavka->kod = $this->leadingZeros($objednavka->id_oblast,2) . $datum->format('m') . $datum->format('y') . $this->leadingZeros($posledni_kod,4);
        
        $objednavka->poznamka = $form['poznamka']->getValue();
        
        $id_objednavka = $this->model->addObjednavka($objednavka);
        
        if ($id_objednavka == false)
        {
            $this->flashMessage('Objednávku se nepodařilo uložit.','error');
            return;
        }
        
        $objednavka->id_objednavka = $id_objednavka;
        $this->cena_s_dph = 0;
        $this->cena_bez_dph = 0;
        $this->body = 0;
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "ASC"), array('id_kategorie' => $kat->id_kategorie));
            foreach ($zbozi as $zboz)
            {
                $val = $form['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->getValue(); 
                if (is_numeric($val))
                {
                    $this->body = $this->body + $val * $zboz->body;                    
                    $this->cena_bez_dph = $this->cena_bez_dph + $val * $zboz->prodejni_cena;
                    $this->cena_s_dph = $this->cena_s_dph + round($val* ($zboz->prodejni_cena + $zboz->prodejni_cena /100 * $zboz->dph), 1);
                    $objednavka->pridatZboziDoObjednavky($zboz->id_zbozi, $val);
                }
            }
        }
        
        $objednavka->setCena($this->cena_bez_dph, $this->cena_s_dph, $this->body);
        $this->redirect('TiskObjednavek:default', $id_objednavka);
    }
    
    /**
     * Show next customer's automat
     * @param type $id_automat id of automat to show
     */
    public function handleNext($id_automat)
    {
        $this->id_automat = $id_automat;
        if (!$this->isAjax())
            $this->redirect('this', $this->id_objednavka, $id_automat);
        else {
            $this->invalidateControl('objAutomat');
            //$this->invalidateControl('nova_objednavka');
        }
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
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div')->class('obj_filtr');
        
        $form->addDatePicker('od', "Od")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y',strtotime($this->filtr_od)));
        $form->addDatePicker('do', "Do")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue(Date('d. m. Y'));

        $form->addSubmit('filtrObjednavky', 'Zobrazit objednávky');
        $form->onSuccess[] = callback($this, 'filtrObjednavky_submit');
        return $form;
    }
    
    /**
     * Filter button of orders
     * @param type $form name of form
     */
    public function filtrObjednavky_submit($form)
    {
        $datum = $form['od']->getValue();
        if ($datum == "")
            $this->filtr_od = NULL;
        else
            $this->filtr_od = $datum->format("Y-n-j");
        
        $datum = $form['do']->getValue();
        if ($datum == "")
            $this->filtr_do = NULL;
        else
            $this->filtr_do = $datum->format("Y-n-j");

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('objHistorie');
        }
    }

    /**
     * Action default
     * @param int $id_zakaznik id of customer we are adding order to
     * @param int $id_automat id of automat we are making order to
     * @param int $id_oblast id of area we are making order to
     */
    public function actionDefault($id_zakaznik, $id_automat = NULL, $id_oblast = NULL) {
        $this->id_zakaznik = $id_zakaznik;
    }

    /**
     * Render default
     * @param int $id_zakaznik id of customer we are adding order to
     * @param int $id_automat id of automat we are making order to
     * @param int $id_oblast id of area we are making order to
     */
    public function renderDefault($id_zakaznik, $id_automat = NULL, $id_oblast = NULL) {
        if (!$this->getUser()->isLoggedIn())
            $this->redirect('sign:in');
        if ($id_zakaznik == NULL)
            $this->redirect('Hlidac:default');
        
        if ($id_oblast != NULL)
            $this->template->id_oblast = $id_oblast;
             
        if ((isset($this->filtr_od) && isset($this->filtr_od)) == false)
        {
            $this->filtr_od = Date('Y-n-j',mktime(0,0,0,date('m')-5,date('d'),date('y')));
            $this->filtr_do = Date('Y-n-j');
        }
        $this->id_zakaznik = $id_zakaznik;
        $this["novaObjednavka"]["id"]->setValue($id_zakaznik);
        if ($id_automat != NULL)
            $this["novaObjednavka"]["id_automat"]->setValue($id_automat);

        $zakaznici = $this -> zakazniciModel -> getZakazniky(NULL, array(
                'id_zakaznik' => $id_zakaznik));
        if (count($zakaznici) == 0)
            $this->redirect('Hlidac:default');
        foreach ($zakaznici as $zakaznik)
        {
            $this->template->zakaznik = $zakaznik;
            break;
        }
        
        // pokud bychom zobrazovali jen platne smlouvy, tak takto:
        //$smlouvy = $this -> smlouvyModel -> getSmlouvy(array("do" => "DSC"), array('id_zakaznik' => $id_zakaznik, array("do > %s", date("Y-m-d"))));
        // my ale zobrazujem vsechny
        $smlouvy = $this -> smlouvyModel -> getSmlouvy(array("do" => "DSC"), array('id_zakaznik' => $id_zakaznik));
        $predelane = array();
        foreach ($smlouvy as $smlouva)
        {
            $temp = new DibiRow(array());

            $datum = new DateTime($smlouva->od);
            $temp->od = $datum->format("j.n.Y");
            $datum = new DateTime($smlouva->do);
            $temp->do = $datum->format("j.n.Y");  
            $temp->minimalni_odber = $smlouva->minimalni_odber;
            $temp->cislo_smlouvy = $smlouva->cislo_smlouvy;
            $temp->zpusob_platby = $smlouva->zpusob_platby;
            $temp->id_smlouva = $smlouva->id_smlouva;
            $predelane[] = $temp;
        }
        $this->template->smlouvy = $predelane;
        
        $automaty = $this -> automatyModel -> getAutomaty(NULL, array(
                'id_zakaznik' => $id_zakaznik));
        $this->template->pocet_stanic = count($automaty);

        // podle poctu automatu urcuju odkazy v rolu ... specialni pripady 0,1,2 a zbytek + vzdy, jestli jsem na prvni strance (tzn nevim id_automatu) nebo jestli pristupuji na nejaky konkretni
        switch (count($automaty))
        {
            case 0:
                $this->flashMessage('U zákazníka se nenachází žádný automat.');
                $this->redirect('ProhledavaniZakazniku:default');
                break;
            case 1:
                foreach ($automaty as $automat)
                {
                    $this->template->automat = $automat;
                    $this->id_automat = $automat->id_automat;
                    $this->template->id_dalsi = $automat->id_automat;
                    $this->template->id_predchozi = $automat->id_automat;
                    //$this->template->kontakty = $this->kontaktyModel->getKontakty(NULL, array('id_'))$automat->id_automat;
                    break;
                }
                break;
           default:
               // pro vice nez 2 automaty
                $automaty = $automaty->fetchAll();
                if ($id_automat != NULL)
                {
                    for ($i = 0; $i < count($automaty); $i++)
                    {
                        if ($automaty[$i]->id_automat == $id_automat)
                        {
                            // kdyz narazim na automat, ktery chci mohou nastat 3 pripady:
                            // 1) je hned ze zacatku
                            // 2) je uprostred
                            // 3) je na konci
                            if ($i == 0)
                            {
                                // na zacatku
                                $this->template->automat = $automaty[$i];
                                $this->id_automat = $automaty[$i]->id_automat;
                                $this->template->id_dalsi = $automaty[1]->id_automat;
                                $this->template->id_predchozi = NULL;
                                break;
                            }
                            else if($i == count($automaty)-1)
                            {
                                // na konci
                                $this->template->automat = $automaty[$i];
                                $this->id_automat = $automaty[$i]->id_automat;
                                $this->template->id_dalsi = NULL;
                                $this->template->id_predchozi = $automaty[$i-1]->id_automat;
                                break;                                
                            }
                            else
                            {
                                // uprostred
                                $this->template->automat = $automaty[$i];
                                $this->id_automat = $automaty[0]->id_automat;
                                $this->template->id_dalsi = $automaty[$i+1]->id_automat;
                                $this->template->id_predchozi = $automaty[$i-1]->id_automat;
                                break;                                
                               
                            } 
                        }

                    }
                }
                else
                {  // kdyz nechceme zadny konkretni, vratime ten prvni a nastavime dalsi a predchozi
                    $this->template->automat = $automaty[0];
                    $this->id_automat = $automaty[0]->id_automat;
                    $this->template->id_dalsi = $automaty[1]->id_automat;
                    $this->template->id_predchozi = NULL;
                }
               break;                    
        }
        // ulozime id oblasti aktualniho automatu do persistentniho parametru presenteru $this->aktualni_oblast
        $this['novaObjednavka']['id_automat']->setValue($this->id_automat);

        $this->template->kontakty = $this -> kontaktyModel -> getKontaktyInContext(NULL, array(
            'id_zakaznik' => $id_zakaznik, 'id_automat' => $this->template->automat->id_automat));

        /************ HISTORIE ********************/
        $kategorie = $this->kategorieModel->getKategorie();
        $this->template->historie_zbozi = array();
        
        $historie = $this->model->getObjednavkyOdDo(array("datum" => "DESC", "id_objednavka" => "DESC"), array('id_zakaznik' => $id_zakaznik), NULL, NULL, $this->filtr_od, $this->filtr_do);
        
        $diff = abs(strtotime($this->filtr_do) - strtotime($this->filtr_od));
        $years = floor($diff / (365*60*60*24));
        $pocet_mesicu = $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        if ($pocet_mesicu == 0)
            $pocet_mesicu = 1;

        $predelane = array();
        $i=0;
        
        // nastavime historii na 0
        $celkova_cena_bez_dph = 0;
        $celkova_cena_s_dph = 0;
        $celkem_body = 0;

        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "DESC"), array('id_kategorie' => $kat->id_kategorie));
            foreach ($zbozi as $zboz)
            {
                $historie_shrnuti[$zboz->id_zbozi] = 0;
                
            }
        }
        
        foreach ($historie as $zaznam)
        {
            $output = "";
            foreach ($kategorie as $kat)
            {
                $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "DESC"), array('id_kategorie' => $kat->id_kategorie));
                $output = $output . '<div class="hist_kategorie">';
                $output = $output . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
                foreach ($zbozi as $zboz)
                {
                    $output = $output . '<div class="historie_prvek">';
                    $output = $output . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";
                    
                    $val = Objednavka::jeZboziVObjednavce($zboz->id_zbozi, $zaznam->id_objednavka);
                    
                    /***** zaznamename do shrnuti ****/
                    $historie_shrnuti[$zboz->id_zbozi] = $historie_shrnuti[$zboz->id_zbozi] + $val;
                    
                    if ($val != false)
                    {
                        $output = $output . '<div class="hist_value vyplneno">' . $val . "</div>";
                        $celkem_body = $celkem_body + $val * $zboz->body;
                        $celkova_cena_bez_dph = $celkova_cena_bez_dph + $val * $zboz->prodejni_cena;
                        $celkova_cena_s_dph = $celkova_cena_s_dph + round($val* ($zboz->prodejni_cena + $zboz->prodejni_cena /100 * $zboz->dph), 1);
                    }
                    else
                        $output = $output . '<div class="hist_value">' . "&nbsp;" . "</div>";
                    $output = $output . '</div>';
                }
                $output = $output . "</div>";
            }

            $this->template->historie_zbozi[$i] = $output;
            
            $temp = new DibiRow(array());

            $datum = new DateTime($zaznam->datum);
            $temp->datum = $datum->format("j.n.Y");
            $temp->poznamka = $zaznam->poznamka;
            $temp->cena_s_dph = $zaznam->cena_s_dph;
            $temp->cena_bez_dph = $zaznam->cena_bez_dph;
            $temp->body = $zaznam->body;
            $predelane[] = $temp;
            $i++;
        }
        $this->template->celkova_cena_bez_dph = $celkova_cena_bez_dph;
        $this->template->celkova_cena_s_dph = $celkova_cena_s_dph;
        $this->template->celkem_body = $celkem_body;

        $this->template->historie = $predelane;

        /*************** Historie statistika *******************/
        $output = "";   // soucet
        $output2 = "";  // prumer
        $output3 = "";  // prumer
        $output4 = "";  // prumer
        $celkovy_zisk = 0;
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "DESC"), array('id_kategorie' => $kat->id_kategorie));
            $output = $output . '<div class="hist_kategorie">';
            $output2 = $output2 . '<div class="hist_kategorie">';
            $output3 = $output3 . '<div class="hist_kategorie">';
            $output4 = $output4 . '<div class="hist_kategorie">';
            $output = $output . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
            $output2 = $output2 . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
            $output3 = $output3 . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';;
            $output4 = $output4 . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';;
            foreach ($zbozi as $zboz)
            {
                // soucet
                $output = $output . '<div class="historie_prvek">';
                $output = $output . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                {
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                    {
                        $output = $output . '<div class="hist_value vyplneno">' . $historie_shrnuti[$zboz->id_zbozi] . "</div>";
                        $celkovy_zisk += ($zboz->prodejni_cena - $zboz->nakupni_cena) * $historie_shrnuti[$zboz->id_zbozi];
                    }
                    else
                        $output = $output . '<div class="hist_value">' . $historie_shrnuti[$zboz->id_zbozi] . "</div>";
                }
                else
                    $output = $output . '<div class="hist_value">'. "--" . "</div>";
                $output = $output . '</div>';
                
                // prumer
                $output2 = $output2 . '<div class="historie_prvek">';
                $output2 = $output2 . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                {
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                        $output2 = $output2 . '<div class="hist_value vyplneno">' . round($historie_shrnuti[$zboz->id_zbozi] / count($historie), 2) . "</div>";
                    else
                        $output2 = $output2 . '<div class="hist_value">' . round($historie_shrnuti[$zboz->id_zbozi] / count($historie), 2) . "</div>";
                }
                else
                    $output2 = $output2 . '<div class="hist_value">' . '--' . "</div>";
                $output2 = $output2 . '</div>';
                
                // mesicni prumer
                $output3 = $output3 . '<div class="historie_prvek">';
                $output3 = $output3 . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                {
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                        $output3 = $output3 . '<div class="hist_value vyplneno">' . round($historie_shrnuti[$zboz->id_zbozi] / $pocet_mesicu,2) . "</div>";
                    else
                        $output3 = $output3 . '<div class="hist_value">' . round($historie_shrnuti[$zboz->id_zbozi] / $pocet_mesicu, 2) . "</div>";
                }
                else
                    $output3 = $output3 . '<div class="hist_value">' . '--' . "</div>";
                $output3 = $output3 . '</div>';
                
                // ziskovost
                $output4 = $output4 . '<div class="historie_prvek">';
                $output4 = $output4 . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                {
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                        $output4 = $output4 . '<div class="hist_value vyplneno">' . ($zboz->prodejni_cena - $zboz->nakupni_cena) * $historie_shrnuti[$zboz->id_zbozi] . "</div>";
                    else
                        $output4 = $output4 . '<div class="hist_value">' . 0 . "</div>";
                }
                else
                    $output4 = $output4 . '<div class="hist_value">' . '--' . "</div>";
                $output4 = $output4 . '</div>';
            }
            $output = $output . "</div>";
            $output2 = $output2 . "</div>";
            $output3 = $output3 . "</div>";
            $output4 = $output4 . "</div>";
        }
        $this->template->ziskovost = $celkovy_zisk;
        $this->template->historie_shrnuti_soucet = $output;
        $this->template->historie_shrnuti_prumer = $output2;
        $this->template->historie_mesic_shrnuti_prumer = $output3;
        $this->template->ziskovost_zbozi = $output4;
        
        /*********** Prepocitavani objednavky *************/
        $form = $this["novaObjednavka"];
        $this->template->cena_s_dph = $this->cena_s_dph;
        $this->template->cena_bez_dph = $this->cena_bez_dph;
        $this->template->zisk = $this->zisk;
        $this->template->body = $this->body;
    }
    
    /************* EDITACE *******************/
    /**
     * Form for editting order
     * @param type $name name of form
     * @return Form for FW
     */
    public function createComponentEditObjednavka($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addProtection("Platnost formuláře vypršela, zkuste to prosím znovu.");
        $renderer = $form->getRenderer();
        
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = \Nette\Utils\Html::el('div')->class('prvek');
        
        $form->addGroup("")->setOption('container', Html::el('div')->class('obj_datum'));
        $form->addDatePicker('datum', "Datum: ")
            ->addRule(Form::VALID, 'Zadané datum není platné.')
            ->setDefaultValue(date("d. m. Y"));
        
        $zakaznik = new Zakaznik();
        $zakaznik->id_zakaznik = $this->id_zakaznik;
        $zakaznik->fetch();
        $res = $zakaznik->getZboziZakaznika()->fetchPairs('id_zbozi', 've_smlouve');
        
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "ASC"), array('id_kategorie' => $kat->id_kategorie));
            $form->addGroup($kat->nazev)->setOption('container', Html::el('fieldset')->class('obj_group'));
            foreach ($zbozi as $zboz)
            {
                // pokud zakaznik ma zbozi ve smlouve, tak ho vykreslime s class red
                if (isset($res[$zboz->id_zbozi])==true)
                {
                    if ($res[$zboz->id_zbozi]==1)
                        $form->addText('nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi, $zboz->zkratka)->setAttribute('autoComplete', "off")->getLabelPrototype()->class('obj_label red');
                    else
                        $form->addText('nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi, $zboz->zkratka)->setAttribute('autoComplete', "off")->getLabelPrototype()->class('obj_label yellow');
                }
                else
                    $form->addText('nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi, $zboz->zkratka)->setAttribute('autoComplete', "off")->getLabelPrototype()->class('obj_label');
                $form['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->getControlPrototype()->class('obj_zbozi');
            }
        }
        
        $form->setCurrentGroup(NULL);
        $form->addTextArea('poznamka', 'Poznámka:',45,2)->getLabelPrototype()->class('objednavka_poznamka');
        
        // POC
        $pole = array();
        foreach ($this->smlouvyModel->getSmlouvy(NULL, array("id_zakaznik" => $zakaznik->id_zakaznik)) as $key => $value)
            $pole[$value->id_smlouva]=$value->poc;
        $form->addSelect('id_smlouva', 'POC:', $pole);
        
        $recalc = $form->addSubmit('prepocitat', 'Přepočítat cenu');        
        $recalc->onClick[] = callback($this, 'prepocitatCenuEdit_submit');
        $recalc->getControlPrototype()->class('hidden');
        $form->addSubmit('novaObjednavka', 'Uložit změny')->onClick[] = callback($this, 'editObjednavka_submit');
        
        $form->addHidden('id');
        $form->addHidden('id_automat');
        
        //$form->onSuccess[] = array($this, '');
        return $form;
    }
    
    /**
     * Button for saving changes in order
     * @param type $button button name
     * @return type returns when something fails
     */
    public function editObjednavka_submit($button)
    {
        $form = $this["editObjednavka"];
        $objednavka = new Objednavka();

        $objednavka->id_objednavka = $form['id']->getValue();
        
        // informace o automatu, kteremu se objednavka priradila
        $automat = new Automat();
        $automat->id_automat = $form['id_automat']->getValue();
        $automat->fetch();
        // info kvuli hledani
        $objednavka->hledani_bmb = $automat->bmb;
        $objednavka->hledani_vyrobni_cislo = $automat->vyrobni_cislo;
        $objednavka->id_oblast = $automat->id_oblast;
        $objednavka->id_smlouva = $form['id_smlouva']->getValue();
        
        // zbytek objednavky
        $datum = $form['datum']->getValue();
        $objednavka->datum = $datum->format('Y-n-j');
        $objednavka->poznamka = $form['poznamka']->getValue();
        // $objednavka->kod zustava nezmenen a je uz vyplneny diky $objednavka->fetch();
        // $objednavka->id_zakaznik take nezmeneno
        
        // pridame zbozi z objednavky zpatky do skladu
        $objednavka->vratitZboziDoSkladu();

        // editujeme objednavku
        if ($objednavka->save()==false)
        {
            $this->flashMessage('Objednávku se nepodařilo uložit.','error');
            $this->redirect("Objednavky:seznam");
            return;
        }
        
        $this->cena_s_dph = 0;
        $this->cena_bez_dph = 0;
        $this->body = 0;
        $kategorie = $this->kategorieModel->getKategorie();
        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "ASC"), array('id_kategorie' => $kat->id_kategorie));
            foreach ($zbozi as $zboz)
            {
                $val = $form['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->getValue(); 
                if (is_numeric($val))
                {
                    $this->cena_bez_dph = $this->cena_bez_dph + $val * $zboz->prodejni_cena;
                    $this->cena_s_dph = $this->cena_s_dph + round($val* ($zboz->prodejni_cena + $zboz->prodejni_cena /100 * $zboz->dph), 1);
                    $this->body = $this->body + $val * $zboz->body;
                    $objednavka->pridatZboziDoObjednavky($zboz->id_zbozi, $val);
                }
            }
        }
        
        $objednavka->setCena($this->cena_bez_dph, $this->cena_s_dph, $this->body);
        
        $this->redirect('this');
    }

    /**
     * Action edit
     * @param type $id id of editted order
     * @param type $id_automat id of automat which editted order belongs to
     */
    public function actionEdit($id, $id_automat = NULL) {
        $this->id_objednavka = $id;
    }

    /**
     * Render edit
     * @param type $id id of editted order
     * @param type $id_automat id of automat which editted order belongs to
     */    
    public function renderEdit($id, $id_automat = NULL) {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');
        
        if ($id == NULL)
            $this->redirect('Hlidac:default');
        
        if ((isset($this->filtr_od) && isset($this->filtr_od)) == false)
        {
            $this->filtr_od = Date('Y-n-j',mktime(0,0,0,date('m')-5,date('d'),date('y')));
            $this->filtr_do = Date('Y-n-j');
        }

        $objednavka = new Objednavka();
        $objednavka->id_objednavka = $id;
        if ($objednavka->fetch() == false)
        {
            $this->flashMessage('Objednávka nebyla nalezena.');
            $this->redirect('Objednavky:seznam');
            return;
        }
        $id_zakaznik = $objednavka->id_zakaznik;
        $this->id_zakaznik = $id_zakaznik;
        $this["editObjednavka"]["id"]->setValue($id);
        $this["editObjednavka"]["datum"]->setValue($objednavka->datum);
        $this["editObjednavka"]["poznamka"]->setValue($objednavka->poznamka);
        $this["editObjednavka"]["id_smlouva"]->setValue($objednavka->id_smlouva);
        
        $zakaznici = $this -> zakazniciModel -> getZakazniky(NULL, array(
                'id_zakaznik' => $id_zakaznik));
        foreach ($zakaznici as $zakaznik)
        {
            $this->template->zakaznik = $zakaznik;
            break;
        }
        
        // pouze aktualni smlouvy:
        //$smlouvy = $this -> smlouvyModel -> getSmlouvy(array("do" => "DSC"), array('id_zakaznik' => $id_zakaznik, array("do > %s", date("Y-m-d"))));
        // pozadavek je ale na vsechny smlouvy:
        $smlouvy = $this -> smlouvyModel -> getSmlouvy(array("do" => "DSC"), array('id_zakaznik' => $id_zakaznik));
        
        $predelane = array();
        foreach ($smlouvy as $smlouva)
        {
            $temp = new DibiRow(array());

            $datum = new DateTime($smlouva->od);
            $temp->od = $datum->format("j.n.Y");
            $datum = new DateTime($smlouva->do);
            $temp->do = $datum->format("j.n.Y");  
            $temp->minimalni_odber = $smlouva->minimalni_odber;
            $temp->cislo_smlouvy = $smlouva->cislo_smlouvy;
            $temp->zpusob_platby = $smlouva->zpusob_platby;
            $temp->id_smlouva = $smlouva->id_smlouva;
            $predelane[] = $temp;
        }
        $this->template->smlouvy = $predelane;
        
        $automaty = $this -> automatyModel -> getAutomaty(NULL, array(
                'id_zakaznik' => $id_zakaznik));
        $this->template->pocet_stanic = count($automaty);

        // podle poctu automatu urcuju odkazy v rolu ... specialni pripady 0,1,2 a zbytek + vzdy, jestli jsem na prvni strance (tzn nevim id_automatu) nebo jestli pristupuji na nejaky konkretni
        switch (count($automaty))
        {
            case 0:
                $this->flashMessage('U zákazníka se nenachází žádný automat.');
                $this->redirect('ProhledavaniZakazniku:default');
                break;
            case 1:
                foreach ($automaty as $automat)
                {
                    $this->template->automat = $automat;
                    $this->template->id_dalsi = $automat->id_automat;
                    $this->template->id_predchozi = $automat->id_automat;
                    //$this->template->kontakty = $this->kontaktyModel->getKontakty(NULL, array('id_'))$automat->id_automat;
                    break;
                }
                break;
            /*case 2:
                $automaty = $automaty->fetchAll();
                if ($id_automat != NULL)
                {
                    if ($id_automat == $automaty[0]->id_automat)
                    {
                        $this->template->automat = $automaty[0];
                        $this->template->id_dalsi = $automaty[1]->id_automat;
                        $this->template->id_predchozi = $automaty[1]->id_automat;            
                    }
                    else
                    {
                        $this->template->automat = $automaty[1];
                        $this->template->id_dalsi = $automaty[0]->id_automat;
                        $this->template->id_predchozi = $automaty[0]->id_automat;            
                    }
                }
                else
                {
                    $this->template->automat = $automaty[0];
                    $this->template->id_dalsi = $automaty[1]->id_automat;
                    $this->template->id_predchozi = $automaty[1]->id_automat;
                }
                break;*/
           default:
               // pro vice nez 2 automaty
                $automaty = $automaty->fetchAll();
                if (isset($id_automat) && $id_automat != NULL)
                {
                    for ($i = 0; $i < count($automaty); $i++)
                    {
                        if ($automaty[$i]->id_automat == $id_automat)
                        {
                            // kdyz narazim na automat, ktery chci mohou nastat 3 pripady:
                            // 1) je hned ze zacatku
                            // 2) je uprostred
                            // 3) je na konci
                            if ($i == 0)
                            {
                                // na zacatku
                                $this->template->automat = $automaty[$i];
                                $this->template->id_dalsi = $automaty[1]->id_automat;
                                $this->template->id_predchozi = NULL;
                                break;
                            }
                            else if($i == count($automaty)-1)
                            {
                                // na konci
                                $this->template->automat = $automaty[$i];
                                $this->template->id_dalsi = NULL;
                                $this->template->id_predchozi = $automaty[$i-1]->id_automat;
                                break;                                
                            }
                            else
                            {
                                // uprostred
                                $this->template->automat = $automaty[$i];
                                $this->template->id_dalsi = $automaty[$i+1]->id_automat;
                                $this->template->id_predchozi = $automaty[$i-1]->id_automat;
                                break;                                
                               
                            } 
                        }

                    }
                }
                else
                {  // kdyz nechceme zadny konkretni, vratime ten prvni a nastavime dalsi a predchozi
                    $this->template->automat = $automaty[0];
                    $this->template->id_dalsi = $automaty[1]->id_automat;
                    $this->template->id_predchozi = NULL;
                }
               break;                    
        }
        // ulozime id oblasti aktualniho automatu do persistentniho parametru presenteru $this->aktualni_oblast
        $this['editObjednavka']['id_automat']->setValue($this->template->automat["id_automat"]);

        $this->template->kontakty = $this -> kontaktyModel -> getKontaktyInContext(NULL, array(
            'id_zakaznik' => $id_zakaznik, 'id_automat' => $this->template->automat->id_automat));

        /************ HISTORIE ********************/
        $kategorie = $this->kategorieModel->getKategorie();
        $this->template->historie_zbozi = array();
        
        $diff = abs(strtotime($this->filtr_do) - strtotime($this->filtr_od));
        $years = floor($diff / (365*60*60*24));
        $pocet_mesicu = $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        if ($pocet_mesicu == 0)
            $pocet_mesicu = 1;
        
        $historie = $this->model->getObjednavkyOdDo(array("datum" => "DESC", "id_objednavka" => "DESC"), array('id_zakaznik' => $id_zakaznik), NULL, NULL, $this->filtr_od, $this->filtr_do);

        $predelane = array();
        $i=0;
        
        // nastavime historii na 0
        $celkova_cena_bez_dph = 0;
        $celkova_cena_s_dph = 0;
        $celkem_body = 0;

        foreach ($kategorie as $kat)
        {
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "DESC"), array('id_kategorie' => $kat->id_kategorie));
            foreach ($zbozi as $zboz)
                $historie_shrnuti[$zboz->id_zbozi] = 0;
        }
        
        foreach ($historie as $zaznam)
        {
            $output = "";
            foreach ($kategorie as $kat)
            {
                $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "DESC"), array('id_kategorie' => $kat->id_kategorie));
                $output = $output . '<div class="hist_kategorie">';
                $output = $output . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
                foreach ($zbozi as $zboz)
                {
                    $output = $output . '<div class="historie_prvek">';
                    $output = $output . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";
                    
                    $val = Objednavka::jeZboziVObjednavce($zboz->id_zbozi, $zaznam->id_objednavka);
                    
                    /***** zaznamename do shrnuti ****/
                    $historie_shrnuti[$zboz->id_zbozi] = $historie_shrnuti[$zboz->id_zbozi] + $val;
                    
                    if ($val != false)
                    {
                        $output = $output . '<div class="hist_value vyplneno">' . $val . "</div>";
                        $celkem_body = $celkem_body + $val * $zboz->body;
                        $celkova_cena_bez_dph = $celkova_cena_bez_dph + $val * $zboz->prodejni_cena;
                        $celkova_cena_s_dph = $celkova_cena_s_dph + round($val* ($zboz->prodejni_cena + $zboz->prodejni_cena /100 * $zboz->dph), 1);
                    }
                    else
                        $output = $output . '<div class="hist_value">' . "&nbsp;" . "</div>";
                    $output = $output . '</div>';
                }
                $output = $output . "</div>";
            }
            $this->template->celkova_cena_bez_dph = $celkova_cena_bez_dph;
            $this->template->celkova_cena_s_dph = $celkova_cena_s_dph;
            $this->template->celkem_body = $celkem_body;

            $this->template->historie_zbozi[$i] = $output;
            
            $temp = new DibiRow(array());

            $datum = new DateTime($zaznam->datum);
            $temp->datum = $datum->format("j.n.Y");
            $temp->poznamka = $zaznam->poznamka;
            $temp->cena_s_dph = $zaznam->cena_s_dph;
            $temp->cena_bez_dph = $zaznam->cena_bez_dph;
            $temp->body = $zaznam->body;
            $predelane[] = $temp;
            $i++;
        }
        $this->template->historie = $predelane;

        /*************** Historie statistika *******************/
        $output = "";   // soucet
        $output2 = "";  // prumer
        $output3 = "";  // mesicni prumer
        $output4 = "";  // ziskovost
        $celkovy_zisk = 0;
        foreach ($kategorie as $kat)
        {
            // ---!!!!!!!!!!!!!!!!!!!!!!!!!---- ZDE vyplnuju data i do objednavky
            
            // a ted historie
            $zbozi = $this->zboziModel->getZbozi(array("id_zbozi" => "DESC"), array('id_kategorie' => $kat->id_kategorie));
            $output = $output . '<div class="hist_kategorie">';
            $output2 = $output2 . '<div class="hist_kategorie">';
            $output3 = $output3 . '<div class="hist_kategorie">';
            $output4 = $output4 . '<div class="hist_kategorie">';
            $output = $output . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
            $output2 = $output2 . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
            $output3 = $output3 . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
            $output4 = $output4 . '<div class="hist_kategorie_nazev">' . $kat->nazev . '</div>';
            foreach ($zbozi as $zboz)
            {
                // kdyz uz iteruju, tak zapisu i data do formulare objednavky
                $this["editObjednavka"]['nazev_' . $kat->id_kategorie . '_' . $zboz->id_zbozi]->setValue(Objednavka::jeZboziVObjednavce($zboz->id_zbozi, $id));
                
                // soucet
                $output = $output . '<div class="historie_prvek">';
                $output = $output . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                    {
                        $output = $output . '<div class="hist_value vyplneno">' . $historie_shrnuti[$zboz->id_zbozi] . "</div>";
                        $celkovy_zisk += ($zboz->prodejni_cena - $zboz->nakupni_cena) * $historie_shrnuti[$zboz->id_zbozi];
                    }
                    else
                        $output = $output . '<div class="hist_value">' . $historie_shrnuti[$zboz->id_zbozi] . "</div>";
                else
                    $output = $output . '<div class="hist_value">'. "--" . "</div>";
                $output = $output . '</div>';
                
                // prumer
                $output2 = $output2 . '<div class="historie_prvek">';
                $output2 = $output2 . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                        $output2 = $output2 . '<div class="hist_value vyplneno">' . round($historie_shrnuti[$zboz->id_zbozi] / count($historie), 2) . "</div>";
                    else
                        $output2 = $output2 . '<div class="hist_value">' . round($historie_shrnuti[$zboz->id_zbozi] / count($historie), 2) . "</div>";
                else
                    $output2 = $output2 . '<div class="hist_value">' . '--' . "</div>";
                $output2 = $output2 . '</div>';
                
                // mesicni prumer
                $output3 = $output3 . '<div class="historie_prvek">';
                $output3 = $output3 . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                {
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                        $output3 = $output3 . '<div class="hist_value vyplneno">' . round($historie_shrnuti[$zboz->id_zbozi] / $pocet_mesicu,2) . "</div>";
                    else
                        $output3 = $output3 . '<div class="hist_value">' . round($historie_shrnuti[$zboz->id_zbozi] / $pocet_mesicu, 2) . "</div>";
                }
                else
                    $output3 = $output3 . '<div class="hist_value">' . '--' . "</div>";
                $output3 = $output3 . '</div>';
                
                // ziskovost
                $output4 = $output4 . '<div class="historie_prvek">';
                $output4 = $output4 . '<div class="hist_nadpis">' . $zboz->zkratka . "</div>";

                if (count($historie) > 0)
                {
                    if ($historie_shrnuti[$zboz->id_zbozi] != 0)
                        $output4 = $output4 . '<div class="hist_value vyplneno">' . ($zboz->prodejni_cena - $zboz->nakupni_cena) * $historie_shrnuti[$zboz->id_zbozi] . "</div>";
                    else
                        $output4 = $output4 . '<div class="hist_value">' . 0 . "</div>";
                }
                else
                    $output4 = $output4 . '<div class="hist_value">' . '--' . "</div>";
                $output4 = $output4 . '</div>';
            }
            $output = $output . "</div>";
            $output2 = $output2 . "</div>";
            $output3 = $output3 . "</div>";
            $output4 = $output4 . "</div>";
        }
        $this->template->ziskovost = $celkovy_zisk;
        $this->template->ziskovost_zbozi = $output4;
        $this->template->historie_shrnuti_soucet = $output;
        $this->template->historie_shrnuti_prumer = $output2;
        $this->template->historie_mesic_shrnuti_prumer = $output3;
        
        /*********** Prepocitavani objednavky *************/
        $this->template->cena_s_dph = $this->cena_s_dph;
        $this->template->cena_bez_dph = $this->cena_bez_dph;
        $this->template->zisk = $this->zisk;
        $this->template->body = $this->body;
        
        $this->id_objednavka = $id;

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
     * AJAX request for deleting order
     * @param type $id id of deleted order
     */
    public function handleDelete($id)
    {
        $zbozi = new Objednavka();
        $zbozi->id_objednavka = urldecode($id);
        $zbozi->delete();
        
        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('stranky');
        }
    }

    /**
     * Action seznam
     */
    public function actionSeznam() {
        
    }

    /**
     * render seznam
     */
    public function renderSeznam() {
        if (!$this->getUser()->isInRole('admin'))
            $this->redirect('sign:in');

        // strankovani
        $vp = new VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 40;
        $paginator->itemCount = count($this -> model -> getObjednavkyHledani($order = array(
                'datum' => 'DESC', "kod" => "DESC"), array("objednavky.hledani_bmb" => $this->filtr_bmb,
                    "objednavky.hledani_vyrobni_cislo" => $this->filtr_vyrobni_cislo,
                    "objednavky.kod" => $this->filtr_objednavky),
                NULL, NULL, $this->filtr_zakaznik));
        $items = $this -> model -> getObjednavkyHledani($order = array(
                'datum' => 'DESC', "kod" => "DESC"), array("objednavky.hledani_bmb" => $this->filtr_bmb,
                    "objednavky.hledani_vyrobni_cislo" => $this->filtr_vyrobni_cislo,
                    "objednavky.kod" => $this->filtr_objednavky),
                $paginator->offset, $paginator->itemsPerPage, $this->filtr_zakaznik);
        
        $this->template->items = $items;
        if ($this->isAjax())
            $this->invalidateControl('stranky');
    }
    
    /**
     * action print - not implemented
     * @param type $id id of printed order
     */
    public function actionPrint($id) {
        
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
    
    /**
     * Singleton ObjednavkyModel
     * @return type 
     */
    public function getZakazniciModel() {
        if(!isset($this->zakazniciModel_var))
            $this->zakazniciModel_var = new ZakazniciModel();

        return $this->zakazniciModel_var;
    }

    /**
     * Singleton SmlouvyModel
     * @return type 
     */
    public function getSmlouvyModel() {
        if(!isset($this->smlouvyModel_var))
            $this->smlouvyModel_var = new SmlouvyModel();

        return $this->smlouvyModel_var;
    }
    
    /**
     * Singleton ZboziModel
     * @return type 
     */
    public function getZboziModel() {
        if(!isset($this->zboziModel_var))
            $this->zboziModel_var = new ZboziModel();

        return $this->zboziModel_var;
    }
    
     /**
     * Singleton AutomatyModel
     * @return type 
     */
   public function getAutomatyModel() {
        if(!isset($this->automatyModel_var))
            $this->automatyModel_var = new AutomatyModel();

        return $this->automatyModel_var;
    }
    
    /**
     * Singleton KontaktyModel
     * @return type 
     */
    public function getKontaktyModel() {
        if(!isset($this->kontaktyModel_var))
            $this->kontaktyModel_var = new KontaktyModel();

        return $this->kontaktyModel_var;
    }

    /**
     * Singleton KategorieModel
     * @return type 
     */
    public function getKategorieModel() {
        if(!isset($this->kategorieModel_var))
            $this->kategorieModel_var = new KategorieModel();

        return $this->kategorieModel_var;
    }
}