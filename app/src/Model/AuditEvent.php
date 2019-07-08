<?php

/**
 * This file contains the "AuditEvent" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2019 <silverstripedev@catalyst.net.nz>
 * @copyright 2019 Catalyst.Net Ltd
 * @license https://www.catalyst.net.nz (Commercial)
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Security\Security;
use NZTA\SDLT\Helper\ClassSpec;
use NZTA\SDLT\Constant\UserGroupConstant;

/**
 * A discrete audit event.
 *
 * See {@link AuditService} and {@link AuditAdmin}.
 */
class AuditEvent extends DataObject
{
    /**
     * @var array
     */
    private static $db = [
        'Event' => DBVarchar::class,
        'Model' => DBVarchar::class,
        'Extra' => DBText::Class,
        'User'  => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Event',
        'Model' => 'Type',
        'User'  => 'User',
        'Created' => 'Audit Date',
    ];

    /**
     * @var string
     */
    private static $table_name = 'AuditEvent';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Main', ReadonlyField::create('Created', 'Logged Date'));

        return $fields;
    }

    /**
     * Internal method to commit a single audit event.
     *
     * @param  string     $event An a single event, declared as a service constant.
     * @param  string     $extra Additional data to save alongside the event-name itself.
     * @param  DataObject $model The model that invoked this commit.
     * @param  string     $user  An identifier of the user that fired the event.
     * @return AuditEvent
     */
    public function log(string $event, string $extra, DataObject $model, $user = '') : AuditEvent
    {
        $this->setField('Event', $event);
        $this->setField('Extra', $extra);
        $this->setField('Model', ClassSpec::short_name(get_class($model)));
        $this->setField('User', $user ?: 'N/A');

        return $this;
    }

    /**
     * @param  mixed Member|null $member Current member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return false;
    }

    /**
     * @param  mixed Member|null $member Current member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return Security::getCurrentUser()
                ->Groups()
                ->find('Code', UserGroupConstant::GROUP_CODE_ADMIN);
    }
}
