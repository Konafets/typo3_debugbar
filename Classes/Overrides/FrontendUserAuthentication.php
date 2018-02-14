<?php namespace Konafets\TYPO3DebugBar\Overrides;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication as BaseFrontendUserAuthentication;

class FrontendUserAuthentication extends BaseFrontendUserAuthentication
{

    public function getSession()
    {
        return $this->sessionData;
    }
}
