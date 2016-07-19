<?php
namespace TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\Link;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**

 */
class EmailViewHelperTest extends \TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\CMS\Fluid\ViewHelpers\Link\EmailViewHelper
	 */
	protected $viewHelper;

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $cObjBackup;

	public function setUp() {
		parent::setUp();
		$GLOBALS['TSFE'] = new \stdClass();
		$GLOBALS['TSFE']->cObj = $this->getMock('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer', array(), array(), '', FALSE);
		$this->viewHelper = $this->getMock($this->buildAccessibleProxy('TYPO3\\CMS\\Fluid\\ViewHelpers\\Link\\EmailViewHelper'), array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderCorrectlySetsTagNameAndAttributesAndContent() {
		$mockTagBuilder = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TagBuilder', array('setTagName', 'addAttribute', 'setContent'));
		$mockTagBuilder->expects($this->once())->method('setTagName')->with('a');
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('href', 'mailto:some@email.tld');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('some content');
		$this->viewHelper->_set('tag', $mockTagBuilder);
		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('some content'));
		$this->viewHelper->initialize();
		$this->viewHelper->render('some@email.tld');
	}

	/**
	 * @test
	 */
	public function renderSetsTagContentToEmailIfRenderChildrenReturnNull() {
		$mockTagBuilder = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TagBuilder', array('setTagName', 'addAttribute', 'setContent'));
		$mockTagBuilder->expects($this->once())->method('setContent')->with('some@email.tld');
		$this->viewHelper->_set('tag', $mockTagBuilder);
		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue(NULL));
		$this->viewHelper->initialize();
		$this->viewHelper->render('some@email.tld');
	}

	/**
	 * @return array
	 */
	public function renderEncodesEmailInFrontendDataProvider() {
		return array(
			'Plain email' => array(
				'some@email.tld',
				0,
				'<a href="mailto:some@email.tld">some@email.tld</a>',
			),
			'Plain email with spam protection' => array(
				'some@email.tld',
				1,
				'<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+tpnfAfnbjm/ume\');">some(at)email.tld</a>',
			),
			'Plain email with ascii spam protection' => array(
				'some@email.tld',
				'ascii',
				'<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#115;&#111;&#109;&#101;&#64;&#101;&#109;&#97;&#105;&#108;&#46;&#116;&#108;&#100;">some(at)email.tld</a>',
			),
			'Susceptible email' => array(
				'"><script>alert(\'email\')</script>',
				0,
				'<a href="mailto:&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;">&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;</a>',
			),
			'Susceptible email with spam protection' => array(
				'"><script>alert(\'email\')</script>',
				1,
				'<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+&quot;&gt;&lt;tdsjqu&gt;bmfsu(\'fnbjm\')&lt;0tdsjqu&gt;\');">&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;</a>',
			),
			'Susceptible email with ascii spam protection' => array(
				'"><script>alert(\'email\')</script>',
				'ascii',
				'<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#34;&#62;&#60;&#115;&#99;&#114;&#105;&#112;&#116;&#62;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#101;&#109;&#97;&#105;&#108;&#39;&#41;&#60;&#47;&#115;&#99;&#114;&#105;&#112;&#116;&#62;">&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;</a>',
			),
		);
	}

	/**
	 * @test
	 * @dataProvider renderEncodesEmailInFrontendDataProvider
	 * @param string $email
	 * @param string $spamProtectEmailAddresses
	 * @param string $expected
	 */
	public function renderEncodesEmailInFrontend($email, $spamProtectEmailAddresses, $expected) {
		/** @var TypoScriptFrontendController $tsfe */
		$tsfe = $this->getMock('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', array('dummy'), array(), '', false);
		$tsfe->cObj = new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
		$tsfe->spamProtectEmailAddresses = $spamProtectEmailAddresses;
		$tsfe->config = array(
			'config' => array(
				'spamProtectEmailAddresses_atSubst' => '',
				'spamProtectEmailAddresses_lastDotSubst' => '',
			),
		);
		$GLOBALS['TSFE'] = $tsfe;
		$mockTagBuilder = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TagBuilder', array('dummy'));
		$mockTagBuilder->setTagName = 'a';
		$viewHelper = $this->getMock($this->buildAccessibleProxy('TYPO3\\CMS\\Fluid\\ViewHelpers\\Link\\EmailViewHelper'), array('isFrontendAvailable', 'renderChildren'));
		$viewHelper->_set('tag', $mockTagBuilder);
		$viewHelper->expects($this->once())->method('isFrontendAvailable')->willReturn(true);
		$viewHelper->expects($this->once())->method('renderChildren')->willReturn(null);
		$viewHelper->initialize();
		$this->assertSame(
			$expected,
			$viewHelper->render($email)
		);
	}

}
