<?php

declare (strict_types=1);
namespace PoP\ComponentModel\ErrorHandling;

use PoP\Translation\Facades\TranslationAPIFacade;
class Error
{
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string|null
     */
    protected $message;
    /**
     * @var array<string, mixed>
     */
    protected $data;
    /**
     * @var Error[]
     */
    protected $nestedErrors;
    public function __construct(string $code, ?string $message = null, ?array $data = null, ?array $nestedErrors = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data ?? [];
        $this->nestedErrors = $nestedErrors ?? [];
    }
    public function getCode() : string
    {
        return $this->code;
    }
    public function getMessage() : ?string
    {
        return $this->message;
    }
    public function getMessageOrCode() : string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        return $this->message ?? \sprintf($translationAPI->__('Error code: \'%s\'', 'component-model'), $this->code);
    }
    /**
     * @return array<string, mixed>
     */
    public function getData() : array
    {
        return $this->data;
    }
    /**
     * @return Error[]
     */
    public function getNestedErrors() : array
    {
        return $this->nestedErrors;
    }
}
