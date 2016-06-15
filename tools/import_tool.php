<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Itav\Component\Mysql\MysqliDriver;

$db = new MysqliDriver('sql.itav.pl', 'inetzone_12', 'INetzone775', 'inetzone_12');

$sql = "select
    md5(b.id) as buyer_id,
    b.lastname,
    b.name,
    b.type,
    b.street,
    b.building,
    b.apartment,
    b.zip,
    b.city,
    b.post_name,
    b.post_street,
    b.post_building,
    b.post_apartment,
    b.post_zip,
    b.post_city,
    b.ten,
    md5(a.id) as id,
    date_format(from_unixtime((a.cdate)), '%Y-%m-%d') as create_date,
    date_format(from_unixtime((a.sdate)), '%Y-%m-%d') as sell_date,
    date_format(from_unixtime(a.sdate + (14 * 24 * 60 * 60)),
            '%Y-%m-%d') as payment_date,
    number,
    numberplanid as number_plan_id,
    md5(customerid) as buyer_id,
    c.itemid as ordinal,
    c.description,
    c.content as unit,
    c.count as quantity,
    c.taxid as tax_id,
    c.value as gross_price
from
    documents a
        inner join
    customers b ON (a.customerid = b.id)
        left join
    invoicecontents c ON (c.docid = a.id)
where
    b.deleted = 0
        and from_unixtime(a.cdate) between '2016-01-01 00:00:00' and '2016-01-31 23:59:59' ";

$db->setEncoding('utf8');
$rows = $db->getAll($sql);

$invRepo = new App\InvoiceRepo();
$intClient = new \App\InteresantClient();
$numRepo = new \App\NumberPlanRepo();
$prodClient = new \App\ProductClient();
$subRepo = new \App\SubscriptionRepo();
$serializer = new \Itav\Component\Serializer\Serializer();

$interesants = [];
$invoices = [];
$seller = $intClient->getInteresantById('575f3d9690941');

foreach ($rows as $row){
        
    $interesant = new App\Interesant();
    $invoice = new \App\Invoice();
    $invoiceItem = new App\InvoiceItem;
    $sub = new \App\Subscription();
    
    $interesant
        ->setId($row['buyer_id'])
        ->setFirstName($row['name'] ? $row['name'] : null )
        ->setLastName($row['type'] == 0 ? $row['lastname'] : null)
        ->setName($row['type'] == 1 ? $row['lastname'] : null)
        ->setType($row['type'] == 1 ? App\Interesant::TYPE_COMPANY : App\Interesant::TYPE_PRIVATE)
        ->setStatus(App\Interesant::STATUS_ACTIVE)
        ->setTen($row['ten']);
    $addresses = [];
    $mainAddr = new App\Address();
    $mainAddr
        ->setCity($row['city'])
        ->setStatus(App\Address::STATUS_ACTIVE)
        ->setStreet($row['street'] . ' ' . $row['building'] . (($row['apartment']) ? ' /'. $row['apartment'] : ''))
        ->setType(App\Address::TYPE_MAIN)
        ->setZip($row['zip']);
    $addresses[] = $mainAddr;
    if($row['post_name']){
        $postAddr = new App\Address();
        $postAddr
            ->setCity($row['post_city'])
            ->setStatus(App\Address::STATUS_ACTIVE)
            ->setStreet($row['post_street'] . ' ' . $row['post_building'] . (($row['post_apartment']) ? ' /'. $row['post_apartment'] : ''))
            ->setType(App\Address::TYPE_POST)
            ->setZip($row['post_zip']);
        $addresses[] = $postAddr;
    }
    $interesant->setAddresses($addresses);
    $st[] = $intClient->saveInteresant($interesant);
    
    $invoice
        ->setId($row['id'])
        ->setCreateDate(new \DateTime($row['create_date']))
        ->setSellDate(new \DateTime($row['sell_date']))
        ->setPaymentDate(new \DateTime($row['payment_date']))
        ->setPaymentType('przelew')
        ->setSeller($seller)
        ->setBuyer($intClient->getInteresantById($row['buyer_id']))
        ->setNumber($row['number'])
        ->setNumberPlanId($row['number_plan_id']);
    $plan = $numRepo->find($invoice->getNumberPlanId());
    $invoice->setDisplayNumber($plan->prepare($invoice->getNumber(), $invoice->getCreateDate()));
    
    $tax = $prodClient->getTaxById($row['tax_id']);
    $invItem = new \App\InvoiceItem();
    $invItem
        ->setOrdinal($row['ordinal'])
        ->setDescription($row['description'])
        ->setUnit($row['unit'])
        ->setQuantity($row['quantity'])
        ->setTax($tax);
    
    $gross = round($invItem->getQuantity() * $row['gross_price'],2);
    $rate = $invItem->getTax()->getRate();
    $taxValue = round(($gross * $rate) / (100 + $rate),2);
    $net = round($gross - $taxValue,2);
    $netPrice = round($net / $invItem->getQuantity(), 2);
    
    $invItem
        ->setNetPrice($netPrice)
        ->setNetValue($net)
        ->setTaxValue($taxValue)
        ->setGrossValue($gross);
    $invoice->addInvoiceItem($invItem);
    \App\InvoiceLogic::calculateInvoice($invoice);
    $st[] = $invRepo->save($invoice);    
    
    $sub
        ->setFrom(new \DateTime('2016-01-01 00:00:00'))
        ->setTo(new \DateTime('2016-12-31 23:59:59'))
        ->setId(md5($invoice->getId()))
        ->setInvoiceId($invoice->getId())
        ->setPeriod(\App\Subscription::PERIOD_MONTHLY)
        ->setStatus(\App\Subscription::STATUS_ACTIVE);
    
    $st[] = $subRepo->save($sub);
        
    
    
}
print_r($st);