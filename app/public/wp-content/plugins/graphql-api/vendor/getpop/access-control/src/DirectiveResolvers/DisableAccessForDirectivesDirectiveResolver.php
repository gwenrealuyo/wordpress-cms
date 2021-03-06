<?php

declare (strict_types=1);
namespace PoP\AccessControl\DirectiveResolvers;

class DisableAccessForDirectivesDirectiveResolver extends \PoP\AccessControl\DirectiveResolvers\DisableAccessDirectiveResolver
{
    public function getDirectiveName() : string
    {
        return 'disableAccessForDirectives';
    }
    protected function isValidatingDirective() : bool
    {
        return \true;
    }
}
