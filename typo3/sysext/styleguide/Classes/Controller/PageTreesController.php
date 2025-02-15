<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Styleguide\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Styleguide\TcaDataGenerator\Generator;
use TYPO3\CMS\Styleguide\TcaDataGenerator\GeneratorFrontend;
use TYPO3\CMS\Styleguide\TcaDataGenerator\RecordFinder;

/**
 * Styleguide: Page trees submodule
 *
 * @internal
 */
#[AsController]
final class PageTreesController
{
    /**
     * @var non-empty-array<int, string>
     */
    private array $allowedActions = [
        'managePageTrees',
    ];

    /**
     * @var non-empty-array<int, string>
     */
    private array $allowedAjaxActions = [
        'tcaCreate',
        'tcaDelete',
        'frontendCreateWithSets',
        'frontendCreateWithSysTemplate',
        'frontendDelete',
    ];

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {}

    /**
     * Main entry point dispatcher
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $currentAction = $request->getQueryParams()['action'] ?? 'managePageTrees';
        if (!in_array($currentAction, $this->allowedActions, true)
            && !in_array($currentAction, $this->allowedAjaxActions, true)
        ) {
            throw new \RuntimeException('Action not allowed', 1720610774);
        }
        $actionMethodName = $currentAction . 'Action';
        return $this->$actionMethodName($request);
    }

    private function managePageTreesAction(ServerRequestInterface $request): ResponseInterface
    {
        $languageService = $this->getLanguageService();
        $finder = GeneralUtility::makeInstance(RecordFinder::class);
        $demoExists = count($finder->findUidsOfStyleguideEntryPages());
        $demoFrontendExists = count($finder->findUidsOfFrontendPages());

        $view = $this->moduleTemplateFactory->create($request);
        $view->assignMultiple([
            'currentAction' => 'managePageTrees',
            'demoExists' => $demoExists,
            'demoFrontendExists' => $demoFrontendExists,
            'routeIdentifier' => 'styleguide_pagetrees',
        ]);
        $view->setTitle(
            $languageService->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:styleguide'),
            $languageService->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:action.managePageTrees'),
        );
        $view->makeDocHeaderModuleMenu();
        $this->addDocHeaderShortcutButton($view);

        return $view->renderResponse('Backend/ManagePageTrees');
    }

    private function tcaCreateAction(): ResponseInterface
    {
        $finder = GeneralUtility::makeInstance(RecordFinder::class);
        if (count($finder->findUidsOfStyleguideEntryPages())) {
            // Tell something was done here
            $json = [
                'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:tcaCreateActionFailedTitle'),
                'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:tcaCreateActionFailedBody'),
                'status' => ContextualFeedbackSeverity::ERROR,
            ];
        } else {
            $generator = GeneralUtility::makeInstance(Generator::class);
            $generator->create();
            // Tell something was done here
            $json = [
                'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:tcaCreateActionOkTitle'),
                'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:tcaCreateActionOkBody'),
                'status' => ContextualFeedbackSeverity::OK,
            ];
        }
        // And redirect to display action
        return new JsonResponse($json);
    }

    private function tcaDeleteAction(): ResponseInterface
    {
        $generator = GeneralUtility::makeInstance(Generator::class);
        $generator->delete();
        $json = [
            'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:tcaDeleteActionOkTitle'),
            'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:tcaDeleteActionOkBody'),
            'status' => ContextualFeedbackSeverity::OK,
        ];
        return new JsonResponse($json);
    }

    private function frontendCreateWithSetsAction(): ResponseInterface
    {
        $recordFinder = GeneralUtility::makeInstance(RecordFinder::class);
        if (count($recordFinder->findUidsOfFrontendPages())) {
            $json = [
                'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionFailedTitle'),
                'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionFailedBody'),
                'status' => ContextualFeedbackSeverity::ERROR,
            ];
        } else {
            $frontend = GeneralUtility::makeInstance(GeneratorFrontend::class);
            $frontend->create('', 1, true);
            $json = [
                'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionOkTitle'),
                'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionOkBody'),
                'status' => ContextualFeedbackSeverity::OK,
            ];
        }
        return new JsonResponse($json);
    }

    private function frontendCreateWithSysTemplateAction(): ResponseInterface
    {
        $recordFinder = GeneralUtility::makeInstance(RecordFinder::class);
        if (count($recordFinder->findUidsOfFrontendPages())) {
            $json = [
                'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionFailedTitle'),
                'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionFailedBody'),
                'status' => ContextualFeedbackSeverity::ERROR,
            ];
        } else {
            $frontend = GeneralUtility::makeInstance(GeneratorFrontend::class);
            $frontend->create();
            $json = [
                'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionOkTitle'),
                'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendCreateActionOkBody'),
                'status' => ContextualFeedbackSeverity::OK,
            ];
        }
        return new JsonResponse($json);
    }

    private function frontendDeleteAction(): ResponseInterface
    {
        $frontend = GeneralUtility::makeInstance(GeneratorFrontend::class);
        $frontend->delete();
        $json = [
            'title' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendDeleteActionOkTitle'),
            'body' => $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:frontendDeleteActionOkBody'),
            'status' => ContextualFeedbackSeverity::OK,
        ];
        return new JsonResponse($json);
    }

    private function addDocHeaderShortcutButton(ModuleTemplate $moduleTemplate): void
    {
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setDisplayName(sprintf(
                '%s - %s',
                $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:styleguide'),
                $this->getLanguageService()->sL('LLL:EXT:styleguide/Resources/Private/Language/locallang.xlf:action.managePageTrees')
            ))
            ->setRouteIdentifier('styleguide_pagetrees')
            ->setArguments(['action' => 'managePageTrees']);
        $buttonBar->addButton($shortcutButton);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
