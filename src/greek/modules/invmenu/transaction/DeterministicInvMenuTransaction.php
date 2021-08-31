<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 31/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */

declare(strict_types=1);

namespace greek\modules\invmenu\transaction;

use Closure;
use InvalidStateException;

final class DeterministicInvMenuTransaction extends InvMenuTransaction
{

    /** @var InvMenuTransactionResult */
    private InvMenuTransactionResult $result;

    public function __construct(InvMenuTransaction $transaction, InvMenuTransactionResult $result)
    {
        parent::__construct($transaction->getPlayer(), $transaction->getOut(), $transaction->getIn(), $transaction->getAction(), $transaction->getTransaction());
        $this->result = $result;
    }

    public function continue(): InvMenuTransactionResult
    {
        throw new InvalidStateException("Cannot change state of deterministic transactions");
    }

    public function discard(): InvMenuTransactionResult
    {
        throw new InvalidStateException("Cannot change state of deterministic transactions");
    }

    public function then(?Closure $callback): void
    {
        $this->result->then($callback);
    }
}