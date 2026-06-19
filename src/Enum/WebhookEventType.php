<?php

namespace App\Enum;

enum WebhookEventType: string
{
    case OrderCreated = 'order.created';
    case OrderUpdated = 'order.updated';
    case OrderPaid = 'order.paid';
    case OrderShipped = 'order.shipped';
    case OrderDelivered = 'order.delivered';
    case OrderCancelled = 'order.cancelled';
    case InvoiceCreated = 'invoice.created';
    case InvoicePaid = 'invoice.paid';
    case RefundCreated = 'refund.created';
    case RefundProcessed = 'refund.processed';
    case CustomerCreated = 'customer.created';
    case SubscriptionActivated = 'subscription.activated';
}
