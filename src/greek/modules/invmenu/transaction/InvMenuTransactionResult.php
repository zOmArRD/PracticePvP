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

final class InvMenuTransactionResult
{

    /** @var bool */
    private bool $cancelled;

    /** @var Closure|null */
    private ?Closure $post_transaction_callback = null;

    public function __construct(bool $cancelled)
    {
        $this->cancelled = $cancelled;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * Notify when we have escaped from the event stack trace and the
     * client's network stack trace.
     * Useful for sending forms and other stuff that cant be sent right
     * after closing inventory.
     *
     * @param Closure|null $callback
     *
     * @return self
     */
    public function then(?Closure $callback): self
    {
        $this->post_transaction_callback = $callback;
        return $this;
    }

    public function getPostTransactionCallback(): ?Closure
    {
        return $this->post_transaction_callback;
    }
}