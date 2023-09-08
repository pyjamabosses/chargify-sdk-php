<?php

namespace Crucial\Service\Chargify;

class Payment extends AbstractEntity
{
    /**
     * Set amount in cents
     *
     * @param int $amountInCents
     *
     * @return Payment
     */
    public function setAmountInCents($amountInCents)
    {
        $this->setParam('amount_in_cents', $amountInCents);

        return $this;
    }

    /**
     * Set memo
     *
     * @param string $memo
     *
     * @return Payment
     */
    public function setMemo($memo)
    {
        $this->setParam('memo', $memo);

        return $this;
    }

    /**
     * Create payment for subscritpion
     *
     * @param int $subscriptionId
     *
     * @return Payment
     */
    public function create($subscriptionId)
    {
        $service       = $this->getService();
        $rawData       = $this->getRawData(array('payment' => $this->getParams()));
        // $rawData       = $this->getRawData(array('invoice' =>  $this->getParams()));
        $response      = $service->request('subscriptions/' . (int)$subscriptionId . '/payments', 'POST', $rawData);
        $responseArray = $this->getResponseArray($response);

        if (!$this->isError()) {
            $this->_data = $responseArray['payment'];
        } else {
            $this->_data = array();
        }

        return $this;
    }
}