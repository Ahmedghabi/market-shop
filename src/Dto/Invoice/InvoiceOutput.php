<?php

namespace App\Dto\Invoice;

final class InvoiceOutput
{
    public string $id;
    public string $invoiceNumber;
    public string $boutiqueId;
    public ?string $customerId;
    public ?string $orderId;
    public ?string $subscriptionId;
    public string $type;
    public string $status;
    public string $currency;
    public int $subtotal;
    public int $discountTotal;
    public int $taxTotal;
    public int $shippingTotal;
    public int $total;
    public string $issuedAt;
    public ?string $dueDate;
    public ?string $paidAt;
    public ?string $pdfPath;
    public ?string $boutiqueName;
    public ?string $boutiqueEmail;
    public ?string $boutiquePhone;
    public ?string $boutiqueAddress;
    public ?string $customerName;
    public ?string $customerEmail;
    public ?string $customerPhone;
    public ?string $customerAddress;
    public ?string $customerCity;
    public ?string $customerPostalCode;
    public ?string $customerCountry;
    /** @var list<array{productId:?string,description:string,quantity:int,unitPrice:int,discount:int,tax:int,total:int}> */
    public array $items = [];
    public string $createdAt;
    public ?string $updatedAt;
}
