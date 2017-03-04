<?php
namespace Interfrog\Vinou\Command;

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

use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Additional fields provider class for usage with the Scheduler's contact export task
 */
class CacheApiFilesTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{

    protected $receiver;
    protected $sendermail;
    protected $sendername;
    protected $ccreceiver;
    protected $bccreceiver;

    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds an email field
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject)
    {

        $extKey = 'vinou';

        if (!isset($taskInfo['receiver'])) {
            $taskInfo['receiver'] = '';
            if ($parentObject->CMD === 'edit') {
                $taskInfo['receiver'] = $task->receiver;
            }
        }
        if (!isset($taskInfo['sendermail'])) {
            $taskInfo['sendermail'] = '';
            if ($parentObject->CMD === 'edit') {
                $taskInfo['sendermail'] = $task->sendermail;
            }
        }
        if (!isset($taskInfo['sendername'])) {
            $taskInfo['sendername'] = '';
            if ($parentObject->CMD === 'edit') {
                $taskInfo['sendername'] = $task->sendername;
            }
        }
        if (!isset($taskInfo['ccreceiver'])) {
            $taskInfo['ccreceiver'] = '';
            if ($parentObject->CMD === 'edit') {
                $taskInfo['ccreceiver'] = $task->ccreceiver;
            }
        }
        if (!isset($taskInfo['bccreceiver'])) {
            $taskInfo['bccreceiver'] = '';
            if ($parentObject->CMD === 'edit') {
                $taskInfo['bccreceiver'] = $task->bccreceiver;
            }
        }

        $receiverName = 'tx_scheduler[receiver]';
        $receiverId = 'receiver';
        $receiverValue = $taskInfo['receiver'];
        $receiverHtml = '<input type="text" name="' . $receiverName . '" id="' . $receiverId . '" value="' . htmlspecialchars($receiverValue) . '" />';
        $additionalFields[$receiverId] = array(
            'code' => $receiverHtml,
            'label' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheapifiles.field.receiver',
            'cshKey' => '_MOD_tools_txschedulerM1',
            'cshLabel' => $receiverId
        );

        $sendermailName = 'tx_scheduler[sendermail]';
        $sendermailId = 'sendermail';
        $sendermailValue = $taskInfo['sendermail'];
        $sendermailHtml = '<input type="text" name="' . $sendermailName . '" id="' . $sendermailId . '" value="' . htmlspecialchars($sendermailValue) . '" />';
        $additionalFields[$sendermailId] = array(
            'code' => $sendermailHtml,
            'label' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheapifiles.field.sendermail',
            'cshKey' => '_MOD_tools_txschedulerM1',
            'cshLabel' => $sendermailId
        );

        $sendernameName = 'tx_scheduler[sendername]';
        $sendernameId = 'sendername';
        $sendernameValue = $taskInfo['sendername'];
        $sendernameHtml = '<input type="text" name="' . $sendernameName . '" id="' . $sendernameId . '" value="' . htmlspecialchars($sendernameValue) . '" />';
        $additionalFields[$sendernameId] = array(
            'code' => $sendernameHtml,
            'label' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheapifiles.field.sendername',
            'cshKey' => '_MOD_tools_txschedulerM1',
            'cshLabel' => $sendernameId
        );

        $ccreceiverName = 'tx_scheduler[ccreceiver]';
        $ccreceiverId = 'ccreceiver';
        $ccreceiverValue = $taskInfo['ccreceiver'];
        $ccreceiverHtml = '<input type="text" name="' . $ccreceiverName . '" id="' . $ccreceiverId . '" value="' . htmlspecialchars($ccreceiverValue) . '" />';
        $additionalFields[$ccreceiverId] = array(
            'code' => $ccreceiverHtml,
            'label' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheapifiles.field.ccreceiver',
            'cshKey' => '_MOD_tools_txschedulerM1',
            'cshLabel' => $ccreceiverId
        );

        $bccreceiverName = 'tx_scheduler[bccreceiver]';
        $bccreceiverId = 'bccreceiver';
        $bccreceiverValue = $taskInfo['bccreceiver'];
        $bccreceiverHtml = '<input type="text" name="' . $bccreceiverName . '" id="' . $bccreceiverId . '" value="' . htmlspecialchars($bccreceiverValue) . '" />';
        $additionalFields[$bccreceiverId] = array(
            'code' => $bccreceiverHtml,
            'label' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheapifiles.field.bccreceiver',
            'cshKey' => '_MOD_tools_txschedulerM1',
            'cshLabel' => $bccreceiverId
        );
        
        return $additionalFields;
    }

    /**
     * This method checks any additional data that is relevant to the specific task
     * If the task class is not relevant, the method is expected to return TRUE
     *
     * @param array	 $submittedData Reference to the array containing the data submitted by the user
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject)
    {
        $receiver = $submittedData['receiver'];
        $sendermail = $submittedData['sendermail'];
        if(!empty($receiver) && empty($sendermail)){
            $parentObject->addMessage($GLOBALS['LANG']->sL('Define a SENDERMAIL for an error report'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
            return FALSE;       
        }
        $sendername = $submittedData['sendername'];
        if(!empty($receiver) && empty($sendername)){
            $parentObject->addMessage($GLOBALS['LANG']->sL('Define a SENDERNAME for an error report'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
            return FALSE;       
        }
        return true;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches
     *
     * @param array $submittedData Array containing the data submitted by the user
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the current task object
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
    {
        if (!empty($submittedData['receiver'])) {
            $task->receiver = $submittedData['receiver'];
        }
        if (!empty($submittedData['sendermail'])) {
            $task->sendermail = $submittedData['sendermail'];
        }
        if (!empty($submittedData['sendername'])) {
            $task->sendername = $submittedData['sendername'];
        }
        if (!empty($submittedData['ccreceiver'])) {
            $task->ccreceiver = $submittedData['ccreceiver'];
        }
        if (!empty($submittedData['bccreceiver'])) {
            $task->bccreceiver = $submittedData['bccreceiver'];
        }
    }
}