<?php
namespace Vinou\VinouConnector\Command;

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
class CacheExpertiseTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{

    protected $itemsPerTask;
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

        $fieldKeys = ['itemsPerTask','receiver','sendermail','sendername','ccreceiver','bccreceiver'];

        foreach ($fieldKeys as $fieldId) {

            if (!isset($taskInfo[$fieldId])) {
                if ($fieldId == 'itemsPerTask') {
                    $taskInfo[$fieldId] = 30;
                } else {
                    $taskInfo[$fieldId] = '';
                }

                if ($parentObject->CMD === 'edit') {
                    $taskInfo[$fieldId] = $task->{$fieldId};
                }
            }

            $name = 'tx_scheduler['.$fieldId.']';
            $value = $taskInfo[$fieldId];
            $html = '<input type="text" name="' . $name . '" id="' . $fieldId . '" value="' . htmlspecialchars($value) . '" />';

            $additionalFields[$fieldId] = array(
                'code' => $html,
                'label' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheexpertise.field.'.$fieldId,
                'cshKey' => '_MOD_tools_txschedulerM1',
                'cshLabel' => $fieldId
            );
        }
        
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
        foreach ($submittedData as $fieldName => $fieldValue) {
            if (!empty($fieldValue)) {
                $task->{$fieldName} = $fieldValue;
            }
        }
    }
}