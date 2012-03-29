<?php
use Nette\Diagnostics\Debugger; 
/**
 * Description of ExportPresenter
 *
 * @author mist
 */

use Nette\Application\UI\Form;

class ExportPresenter extends BasePresenter {

    private $objednavkyModel_var = NULL;
    private $zakazniciModel_var = NULL;
    private $zboziModel_var = NULL;
    
    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }
    
    public function createComponentNastavitExport($name)
    {
        $form = new Form($this, $name);
        $d = new DateTime();
        $d->modify( 'first day of previous month' );
        $form->addDatePicker('od', "Od")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue($d->format( 'j.m.Y' ));
        $d = new DateTime();
        $d->modify( 'last day of previous month' );
        $form->addDatePicker('doo', "Do")
            ->addRule(Form::VALID, 'Zadané datum není platné.')->setDefaultValue($d->format( 'j.m.Y' ));
        
        $form->addSubmit('novyAutomat', 'Exportovat');
        $form->onSuccess[] = array($this, 'export_submit');
        return $form;
    }

    public function export_submit($form)
    {
        $od = $form['od']->getValue();
        $do = $form['doo']->getValue();

        $od = $od->format( 'Y-m-j' );
        $do = $do->format( 'Y-m-j' );

        $objPHPExcel = new PHPExcel();

        //$objPHPExcel->getProperties()->setCreator("");

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Distributor_customer_code');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Distributor_name');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Date_creation');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Time_creation');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Site_code');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Site_name');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Invoice_number');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'SAP_mat_code');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Material_sold_date');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Material_sold_weight');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Material_name');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Material_price');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'číslo smlouvy');

        // vyplneni tabulky
        $i = 3;
        //$smlouvy = $this -> smlouvyModel -> getSmlouvy(array("do" => "DSC"), array('id_zakaznik' => $id_zakaznik, array("do > %s", date("Y-m-d"))));

        $zakaznici = $this->zakazniciModel->getZakaznikyContext(NULL, array("osobni_zakaznik" => 0, array("id_zakaznik > %i", 0)));
        foreach ($zakaznici as $zakaznik)
        {
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
            $zbozi = $this->zboziModel->getZbozi(NULL, array("nestle" => 1));
            foreach ($zbozi as $zboz)
            {
                $posledni = $this->model->getObjednavkyOdDo(array("datum" => "DSC"),
                                        array("id_zakaznik" => $zakaznik->id_zakaznik),
                                        NULL, 1, $od, $do)->fetchSingle();

                if ($posledni == false)
                    $posledni = "";

                $soucet = 0;
                $data = $this->model->getObjednavkyExport(NULL,
                                        array("id_zakaznik" => $zakaznik->id_zakaznik, "id_zbozi" => $zboz->id_zbozi),
                                        NULL, NULL, $od, $do);
                foreach ($data as $item)
                {
                    $soucet+=$item->pocet;
                }
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $i, date('Ymd'));
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $i, date('His'));
                /*
                if (isset($zakaznik->poc) && $zakaznik->poc != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('E' . $i, $zakaznik->poc);
                if (isset($zakaznik->nazev) && $zakaznik->nazev != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('F' . $i, $zakaznik->nazev);
                if (isset($zboz->sapcode) && $zboz->sapcode != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('H' . $i, $zboz->sapcode);
                if (isset($posledni) && $posledni != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('I' . $i, $posledni);
                if (isset($soucet) && $soucet != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('J' . $i, $soucet);
                if (isset($zboz->nazev) && $zboz->nazev != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('K' . $i, $zboz->nazev);
                if (isset($zboz->nakupni_cena) && $zboz->nakupni_cena != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('L' . $i, $zboz->nakupni_cena);
                if (isset($zakaznik->cislo_smlouvy) && $zakaznik->cislo_smlouvy != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('M' . $i, $zakaznik->cislo_smlouvy);
                */

                  $objPHPExcel->getActiveSheet()->SetCellValue('E' . $i, $zakaznik->poc);
                  $objPHPExcel->getActiveSheet()->SetCellValue('F' . $i, $zakaznik->nazev);
                if (isset($zboz->sapcode) && $zboz->sapcode != "")
                  $objPHPExcel->getActiveSheet()->SetCellValue('H' . $i, $zboz->sapcode);
                  $objPHPExcel->getActiveSheet()->SetCellValue('I' . $i, $posledni);
                  $objPHPExcel->getActiveSheet()->SetCellValue('J' . $i, $soucet);
                  $objPHPExcel->getActiveSheet()->SetCellValue('K' . $i, $zboz->nazev);
                  $objPHPExcel->getActiveSheet()->SetCellValue('L' . $i, $zboz->nakupni_cena);
                  $objPHPExcel->getActiveSheet()->SetCellValue('M' . $i, $zakaznik->cislo_smlouvy);
                  
                $i++;
            }
        }
        
        
        $objPHPExcel->getActiveSheet()->setTitle('Export');

        // Save Excel 2007 file
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $response = Nette\Environment::getHttpResponse();

        $response->setHeader('Content-Type','application/vnd.ms-excel');
        $response->setHeader('Content-Type', 'application/force-download');
        $response->setHeader('Content-Type', 'application/octet-stream');
        $response->setHeader('Content-Type', 'application/download');
        $response->setHeader('Content-Disposition', 'attachment; filename="export.xlsx"');
        $response->setHeader('Content-Transfer-Encoding', 'binary');


        $objWriter->save('php://output');
        $this->terminate();
        
        /* nefunkcni: 
        ob_clean();
        flush();
        $this->terminate();
         * 
         */
        
        
        //$this->redirect('Export:Xls', $od, $do);
    }
    
    public function actionDefault() {
        
    }
    
    public function renderDefault() {
        
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
    
    public function getZakazniciModel() {
        if(!isset($this->zakazniciModel_var))
            $this->zakazniciModel_var = new ZakazniciModel();

        return $this->zakazniciModel_var;
    }
    
    public function getZboziModel() {
        if(!isset($this->zboziModel_var))
            $this->zboziModel_var = new ZboziModel();

        return $this->zboziModel_var;
    }
}