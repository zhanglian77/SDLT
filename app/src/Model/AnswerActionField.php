<?php

/**
 * This file contains the "AnswerActionField" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright 2018 Catalyst.Net Ltd
 * @license https://www.catalyst.net.nz (Commercial)
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\GraphQL\Scaffolding\Interfaces\ScaffoldingProvider;
use SilverStripe\GraphQL\Scaffolding\Scaffolders\SchemaScaffolder;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Convert;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Class AnswerActionField
 *
 * There are different types of action, and the CMS interface will be changed dynamically based on the type
 *
 * @property string Label
 * @property string Title
 * @property string Type
 * @property string Message
 *
 * @property Task Task
 * @property Question Goto
 * @property Question Question
 *
 * @method Task Task()
 * @method Question Goto()
 * @method Question Question()
 */
class AnswerActionField extends DataObject implements ScaffoldingProvider
{
    /**
     * @var string
     */
    private static $table_name = 'AnswerActionField';

    /**
     * @var array
     */
    private static $db = [
        'Label' => 'Varchar(255)',
        'ActionType' => 'Enum(array("continue", "goto", "message", "finish"))',
        'Message' => 'HTMLText',
        'SortOrder' => 'Int',
        'Result' => 'Varchar(255)'
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Task' => Task::class,
        'Goto' => Question::class,
        'Question' => Question::class
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Label',
        'ActionType',
        'ActionDescription',
        'Task.Name' => 'Task'
    ];

    /**
     * @var array
     */
    private static $field_labels = [
        'Label' => 'Action Label'
    ];

    /**
     * @var string
     */
    private static $default_sort = 'SortOrder';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['QuestionID', 'SortOrder']);

        // get questionnaire Id
        $questionnaireID = $this->Question()->Questionnaire()->ID;

        $questionList = Question::get()->filter('QuestionnaireID', $questionnaireID);

        $fields->dataFieldByName('Result')->setDescription('The result will be used only if Questionnaire type is a task.');

        $fields->dataFieldByName('GotoID')->setSource($questionList);

        /** @noinspection PhpUndefinedMethodInspection */
        $fields->dataFieldByName('GotoID')->displayIf('ActionType')->isEqualTo('goto');

        /** @noinspection PhpUndefinedMethodInspection */
        $fields->dataFieldByName('Message')->displayIf('ActionType')->isEqualTo('message');

        /** @noinspection PhpUndefinedMethodInspection */
        $fields->dataFieldByName('Result')->displayIf('ActionType')->isEqualTo('finish');

        return $fields;
    }

    /**
     * @param SchemaScaffolder $scaffolder Scaffolder
     * @return SchemaScaffolder
     */
    public function provideGraphQLScaffolding(SchemaScaffolder $scaffolder)
    {
        // Provide entity type
        $typeScaffolder = $scaffolder
            ->type(AnswerActionField::class)
            ->addFields([
                'ID',
                'Label',
                'ActionType',
                'Message',
                'Task',
                'Goto',
            ]);

        return $scaffolder;
    }

    /**
     * @return string
     */
    public function getActionDescription()
    {
        switch ($this->ActionType) {
            case 'goto':
                $question = $this->Goto->exists() ? $this->Goto->Title : "Null";
                return "Goto: {$question}";
            case 'message':
                return "Message: " . Convert::xml2raw($this->Message);
            case 'continue':
                return 'Continue';
            case 'finish':
                return 'Finish';
            default:
                return "Unknown";
        }
    }

    /**
     * Allow logged-in user to access the model
     *
     * @param Member|null $member member
     * @return bool
     */
    public function canView($member = null)
    {
        return (Security::getCurrentUser() !== null);
    }
}
