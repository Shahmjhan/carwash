<?php

namespace App\Enums;

enum JobStatus: string
{
    case WAITING_FOR_CHECKIN = 'waiting_for_checkin';
    case CHECKED_IN = 'checked_in';
    case INSPECTION_PENDING = 'inspection_pending';
    case INSPECTION_COMPLETED = 'inspection_completed';
    case CUSTOMER_APPROVAL_PENDING = 'customer_approval_pending';
    case APPROVED = 'approved';
    case WAITING_FOR_PARTS = 'waiting_for_parts';
    case IN_SERVICE = 'in_service';
    case QUALITY_CHECK = 'quality_check';
    case COMPLETED = 'completed';
    case READY_FOR_PAYMENT = 'ready_for_payment';
    case PAID = 'paid';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';
    case AWAITING_CUSTOMER = 'awaiting_customer';
    case AWAITING_SUPPLIER = 'awaiting_supplier';

    public function getLabel(): string
    {
        return match($this) {
            self::WAITING_FOR_CHECKIN => 'Waiting for Check-in',
            self::CHECKED_IN => 'Checked In',
            self::INSPECTION_PENDING => 'Inspection Pending',
            self::INSPECTION_COMPLETED => 'Inspection Completed',
            self::CUSTOMER_APPROVAL_PENDING => 'Customer Approval Pending',
            self::APPROVED => 'Approved',
            self::WAITING_FOR_PARTS => 'Waiting for Parts',
            self::IN_SERVICE => 'In Service',
            self::QUALITY_CHECK => 'Quality Check',
            self::COMPLETED => 'Completed',
            self::READY_FOR_PAYMENT => 'Ready for Payment',
            self::PAID => 'Paid',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::ON_HOLD => 'On Hold',
            self::AWAITING_CUSTOMER => 'Awaiting Customer',
            self::AWAITING_SUPPLIER => 'Awaiting Supplier',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::WAITING_FOR_CHECKIN, self::INSPECTION_PENDING, self::CUSTOMER_APPROVAL_PENDING => 'yellow',
            self::CHECKED_IN, self::APPROVED, self::IN_SERVICE => 'blue',
            self::INSPECTION_COMPLETED, self::QUALITY_CHECK => 'purple',
            self::WAITING_FOR_PARTS, self::ON_HOLD, self::AWAITING_CUSTOMER, self::AWAITING_SUPPLIER => 'orange',
            self::COMPLETED, self::READY_FOR_PAYMENT => 'cyan',
            self::PAID, self::DELIVERED => 'green',
            self::CANCELLED => 'red',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        $transitions = [
            self::WAITING_FOR_CHECKIN->value => [self::CHECKED_IN, self::CANCELLED],
            self::CHECKED_IN->value => [self::INSPECTION_PENDING, self::CANCELLED, self::ON_HOLD],
            self::INSPECTION_PENDING->value => [self::INSPECTION_COMPLETED, self::CANCELLED, self::ON_HOLD],
            self::INSPECTION_COMPLETED->value => [self::CUSTOMER_APPROVAL_PENDING, self::APPROVED, self::ON_HOLD],
            self::CUSTOMER_APPROVAL_PENDING->value => [self::APPROVED, self::ON_HOLD, self::CANCELLED],
            self::APPROVED->value => [self::WAITING_FOR_PARTS, self::IN_SERVICE, self::ON_HOLD],
            self::WAITING_FOR_PARTS->value => [self::IN_SERVICE, self::AWAITING_SUPPLIER, self::ON_HOLD, self::CANCELLED],
            self::IN_SERVICE->value => [self::QUALITY_CHECK, self::ON_HOLD, self::AWAITING_CUSTOMER],
            self::QUALITY_CHECK->value => [self::COMPLETED, self::IN_SERVICE], // Can return to service if QC fails
            self::COMPLETED->value => [self::READY_FOR_PAYMENT],
            self::READY_FOR_PAYMENT->value => [self::PAID],
            self::PAID->value => [self::DELIVERED],
            self::DELIVERED->value => [],
            self::CANCELLED->value => [],
            self::ON_HOLD->value => [self::APPROVED, self::IN_SERVICE, self::WAITING_FOR_PARTS, self::CANCELLED],
            self::AWAITING_CUSTOMER->value => [self::IN_SERVICE, self::ON_HOLD, self::CANCELLED],
            self::AWAITING_SUPPLIER->value => [self::WAITING_FOR_PARTS, self::ON_HOLD, self::CANCELLED],
        ];

        return in_array($status, $transitions[$this->value] ?? []);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED]);
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::DELIVERED, self::CANCELLED]);
    }
}
