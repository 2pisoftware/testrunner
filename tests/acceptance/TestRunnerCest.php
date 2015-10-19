<?php
use \AcceptanceGuy;

class TestRunnerCest
{
    public function _before(AcceptanceGuy $I)
    {
    }

    public function _after(AcceptanceGuy $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceGuy $I)
    {
		$I->amOnPage('/');
		$I->see('Codeception PHP Testing Framework');
		$I->see('PhantomJS server stopped');
    }
}
