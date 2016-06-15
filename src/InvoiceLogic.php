<?php

namespace App;

class InvoiceLogic
{
    /**
     * 
     * @param Invoice $invoice
     */
    static public function validate(Invoice $invoice)
    {
        $invoice->getId();
        return true;
    }
    
    /**
     * 
     * @param Invoice $invoice
     */
    static public function calculateInvoice($invoice)
    {
        $productClient = new ProductClient();
        $items = $invoice->getInvoiceItems();
        $taxSummaries = [];

        foreach ($items as $item) {
            $net = round((float) $item->getNetValue(), 2);
            $tax = round((float) $item->getTaxValue(), 2);
            $gross = round((float) $item->getGrossValue(), 2);
            $invoice->setTotalNet($invoice->getTotalNet() + $net);
            $invoice->setTotalTax($invoice->getTotalTax() + $tax);
            $invoice->setTotalGross($invoice->getTotalGross() + $gross);
            $taxid = $item->getTax()->getId();
            $item->setTax($productClient->getTaxById($taxid));
            if (!in_array($taxid, array_keys($taxSummaries))) {
                $taxSummary = new TaxSummary();
                $taxSummary->setTaxName($item->getTax()->getName());

                $taxSummaries[$taxid] = $taxSummary;
            } else {
                $taxSummary = $taxSummaries[$taxid];
            }
            $taxSummary
                    ->setTaxName($item->getTax()->getName())
                    ->setTotalNet($taxSummary->getTotalNet() + $net)
                    ->setTotalTax($taxSummary->getTotalTax() + $tax)
                    ->setTotalGross($taxSummary->getTotalGross() + $gross);
        }
        $invoice->setTaxSummaries(array_values($taxSummaries));
        $invoice->setTotalInWords(self::amountInWords($invoice->getTotalGross()));
    }


    
    static public function preparePrintAddresses(Invoice $invoice)
    {
        $hasMain = false;
        $invoice->getSeller()->getAddresses();
        foreach($invoice->getSeller()->getAddresses() as $key => $address){
            if($address->getType() == Address::TYPE_MAIN){
                $mainSell = $address;
                $hasMain = true;
                break;
            }
        }
        if(!$hasMain){
            $mainSell = $invoice->getSeller()->getAddresses()[0];
        }
        $hasMain = false;
        $hasPost = false;
        foreach($invoice->getBuyer()->getAddresses() as $address){
            if($address->getType() == Address::TYPE_MAIN){
                $mainBuy = $address;
                $hasMain = true;
            }
            if($address->getType() == Address::TYPE_POST){
                $postBuy = $address;
                $hasPost = true;
            }            
        }
        if(!$hasPost){
            $mainBuy = $invoice->getBuyer()->getAddresses()[0];
        }
        if(!$hasPost){
            $postBuy = $mainBuy;
        }
        $invoice->getSeller()->setAddresses([]);
        $invoice->getBuyer()->setAddresses([]);
        $invoice->getSeller()->addAddress($mainSell, 'main');
        $invoice->getBuyer()->addAddress($mainBuy, 'main');
        $invoice->getBuyer()->addAddress($postBuy, 'post');
        return $invoice;
    }
    
    static public function amountInWords($kw) {
        
        $kw = str_replace('.', ',', $kw);
        $kw_slow = null; $kw_w = null;$l_pad = null;

        $t_a = array('', 'sto', 'dwieście', 'trzysta', 'czterysta', 'pięćset', 'sześćset', 'siedemset', 'osiemset', 'dziewięćset');
        $t_b = array('', 'dziesięć', 'dwadzieścia', 'trzydzieści', 'czterdzieści', 'pięćdziesiąt', 'sześćdziesiąt', 'siedemdziesiąt', 'osiemdziesiąt', 'dziewięćdziesiąt');
        $t_c = array('', 'jeden', 'dwa', 'trzy', 'cztery', 'pięć', 'sześć', 'siedem', 'osiem', 'dziewięć');
        $t_d = array('dziesięć', 'jedenaście', 'dwanaście', 'trzynaście', 'czternaście', 'piętnaście', 'szesnaście', 'siednaście', 'osiemnaście', 'dziewiętnaście');

        $t_kw_15 = array('septyliard', 'septyliardów', 'septyliardy');
        $t_kw_14 = array('septylion', 'septylionów', 'septyliony');
        $t_kw_13 = array('sekstyliard', 'sekstyliardów', 'sekstyliardy');
        $t_kw_12 = array('sekstylion', 'sekstylionów', 'sepstyliony');
        $t_kw_11 = array('kwintyliard', 'kwintyliardów', 'kwintyliardy');
        $t_kw_10 = array('kwintylion', 'kwintylionów', 'kwintyliony');
        $t_kw_9 = array('kwadryliard', 'kwadryliardów', 'kwaryliardy');
        $t_kw_8 = array('kwadrylion', 'kwadrylionów', 'kwadryliony');
        $t_kw_7 = array('tryliard', 'tryliardów', 'tryliardy');
        $t_kw_6 = array('trylion', 'trylionów', 'tryliony');
        $t_kw_5 = array('biliard', 'biliardów', 'biliardy');
        $t_kw_4 = array('bilion', 'bilionów', 'bilony');
        $t_kw_3 = array('miliard', 'miliardów', 'miliardy');
        $t_kw_2 = array('milion', 'milionów', 'miliony');
        $t_kw_1 = array('tysiąc', 'tysięcy', 'tysiące');
        $t_kw_0 = array('złoty', 'złotych', 'złote');

        if ($kw != '') {
            $kw = (substr_count($kw, ',') == 0) ? $kw . ',00' : $kw;
            $tmp = explode(",", $kw);
            $ln = strlen($tmp[0]);
            $tmp_a = ($ln % 3 == 0) ? (floor($ln / 3) * 3) : ((floor($ln / 3) + 1) * 3);
            
            for ($i = $ln; $i < $tmp_a; $i++) {
                $l_pad .= '0';
                $kw_w = $l_pad . $tmp[0];
            }
            $kw_w = ($kw_w == '') ? $tmp[0] : $kw_w;
            $paczki = (strlen($kw_w) / 3) - 1;
            $p_tmp = $paczki;
            for ($i = 0; $i <= $paczki; $i++) {
                $t_tmp = 't_kw_' . $p_tmp;
                $p_tmp--;
                $p_kw = substr($kw_w, ($i * 3), 3);
                @$kw_w_s = ($p_kw{1} != 1) ? $t_a[$p_kw{0}] . ' ' . $t_b[$p_kw{1}] . ' ' . $t_c[$p_kw{2}] : $t_a[$p_kw{0}] . ' ' . $t_d[$p_kw{2}];
                if (($p_kw{0} == 0) && ($p_kw{2} == 1) && ($p_kw{1} < 1))
                    $ka = ${$t_tmp}[0]; //możliwe że $p_kw{1}!=1
                else if (($p_kw{2} > 1 && $p_kw{2} < 5) && $p_kw{1} != 1)
                    $ka = ${$t_tmp}[2];
                else
                    $ka = ${$t_tmp}[1];
                $kw_slow.=$kw_w_s . ' ' . $ka . ' ';
            }
        }
        $text = $kw_slow . ' ' . $tmp[1] . '/100 gr.';
        return $text;
    }    
}