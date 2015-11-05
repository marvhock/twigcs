<?php

namespace Allocine\TwigLinter\Helper;

use Allocine\TwigLinter\Lexer;

class CallbackTokenSequence extends TokenSequence
{
    /**
     * @var callable
     */
    private $success;

    /**
     * @var callable
     */
    private $failure;

    /**
     * @param array         $sequence
     * @param callable|null $success
     * @param callable|null $failure
     */
    public function __construct(array $sequence, callable $success = null, callable $failure = null)
    {
        $this->success  = $success;
        $this->failure  = $failure;

        parent::__construct($sequence);
    }

    /**
     * @param \Twig_TokenStream $tokens
     * @param integer           $offset
     *
     * @return boolean
     */
    public function match(\Twig_TokenStream $tokens, $offset = 0)
    {
        $success = parent::match($tokens, $offset);

        if ($success && $this->success) {
            call_user_func($this->success, $this->captures);
        } elseif ($this->failure) {
            call_user_func($this->failure);
        }

        return $success;
    }
}
