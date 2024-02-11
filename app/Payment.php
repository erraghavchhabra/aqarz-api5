<?php


namespace App;

class Payment
{
    protected $customer_token;

    /** @inheritDoc */
    public function getCurrency(): string
    {
        return 'SAR';
    }

    /** @inheritDoc */
    public function getAmount(): float
    {
        return 50;
    }

    /** @inheritDoc */
    public function getTrackingId(): ?string
    {
        return null;
    }

    /** @inheritDoc */
    public function getCustomerPhoneNumber(): ?string
    {
        return '971524633386';
    }

    /** @inheritDoc */
    public function getCustomerEmail(): ?string
    {
        return 'mfarooq@ajar.com.kw';
    }

    /**
     * get the customer token from tokenziation process
     *
     * @return string|null
     */
    public function getCustomerToken(): ?string
    {
        return $this->customer_token;
    }

    public function setCustomerToken(string $customer_token)
    {
        $this->customer_token = $customer_token;
    }

    public function getPaymentLink(): string
    {
        return substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'),0,5);
    }

    /**
     * get the customer ip address
     *
     * @return string|null
     */
    public function getCustomerIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'];
    }
}
