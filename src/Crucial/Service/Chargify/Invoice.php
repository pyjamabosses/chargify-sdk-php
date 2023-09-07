<?php

/**
 * Copyright 2011 Crucial Web Studio, LLC or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * https://raw.githubusercontent.com/chargely/chargify-sdk-php/master/LICENSE.md
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Crucial\Service\Chargify;

class Invoice extends AbstractEntity
{

    /**
     * 
     *
     * @param string $unitPrice
     *
     * @return Charge
     */
    public function setUnitPrice($unitPrice)
    {
        $this->setParam('unit_price', $unitPrice);

        return $this;
    }

    /**
     * 
     *
     * @param string $quantity
     *
     * @return Charge
     */
    public function setQuantity($quantity=1)
    {
        $this->setParam('quantity', $quantity);

        return $this;
    }

    /**
     * (either 'amount' or 'amount_in_cents' is required) If you use this
     * parameter, you should pass the amount represented as a number of cents,
     * either as a string or integer. For example, $10.00 would be represented
     * as 1000. If you pass a value for both 'amount' and 'amount_in_cents, the
     * value in 'amount_in_cents' will be used and 'amount' will be discarded.
     *
     * @param int $amountInCents
     *
     * @return Charge
     */
    public function setAmountInCents($amountInCents)
    {
        $this->setParam('amount_in_cents', $amountInCents);

        return $this;
    }

    /**
     * (required) A helpful explanation for the charge. This amount will remind
     * you and your customer for the reason for the assessment of the charge.
     *
     * @param string $title
     *
     * @return Invoice
     */
    public function setTitle($title)
    {
        $this->setParam('title', $title);

        return $this;
    }

    /**
     * A custom memo can be sent with the memo parameter to override the site's default.
     *
     * @param string $memo
     *
     * @return Invoice
     */
    public function setMemo($memo)
    {
        $this->setParam('memo', $memo);

        return $this;
    }

    /**
     * Send custom payment instructions
     *
     * @param string $payment_instruction
     *
     * @return Invoice
     */
    public function setPaymentInstructions($paymentInstructions)
    {
        $this->setParam('payment_instructions', $paymentInstructions);

        return $this;
    }

    /**
     * For "live" subscriptions (i.e. subscriptions that are not canceled or expired)
     * you have the ability to attach a one-time (or "one-off") charge of an
     * arbitrary amount.Enter description here...
     *
     * https://chargify.api-docs.io/v1/subscriptions-invoices/create-an-invoice
     * 
     * @param int $subscriptionId
     *
     * @return Invoice
     * @see Invoice::setAmount()
     * @see Invoice::setAmountInCents()
     * @see Invoice::setMemo()
     */
    public function create($subscriptionId)
    {
        $service       = $this->getService();
        $rawData       = $this->getRawData(array(
            'invoice' => array(
                'line_items' => array($this->getParams()), 
                'memo' => $this->getParam('memo'),
                'payment_instructions' => $this->getParam('payment_instructions')
            )
        ));
        // $rawData       = $this->getRawData(array('invoice' =>  $this->getParams()));
        $response      = $service->request('subscriptions/' . (int)$subscriptionId . '/invoices', 'POST', $rawData);
        $responseArray = $this->getResponseArray($response);

        if (!$this->isError()) {
            $this->_data = $responseArray['invoice'];
        } else {
            $this->_data = array();
        }

        return $this;
    }
}