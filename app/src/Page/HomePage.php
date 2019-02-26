<?php

/**
 * This file contains the "HomePage" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright 2018 Catalyst.Net Ltd
 * @license https://www.catalyst.net.nz (Commercial)
 * @link https://www.catalyst.net.nz
 */

namespace SDLT\Page;

use Page;
use SDLT\Controller\HomePageController;
use SilverStripe\Security\Permission;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DB;
use SilverStripe\CMS\Controllers\RootURLController;
use SilverStripe\Versioned\Versioned;

/**
 * Class HomePage
 *
 */
class HomePage extends Page
{
    /**
     * @return string
     */
    public function getControllerName()
    {
        return HomePageController::class;
    }

    /**
     * Ensure that only a single home is able to be created in the CMS
     *
     * @param Member $member  default parameter for canCreate
     * @param array  $context Additional context-specific data which might affect
     *                        whether (or where) this object could be created
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        return (parent::canCreate($member) && HomePage::get()->Count() === 0);
    }

    /**
     * Add default records to database.
     *
     * This function is called whenever the database is built, after the
     * database tables have all been created. Overloa this to add default
     * records when the database is built, but make sure you call
     * parent::requireDefaultRecords().
     *
     * @return void
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (SiteTree::config()->create_default_pages) {
            return;
        }

        // default pages
        if (!(HomePage::get()->Count())) {
            $defaultHomepage = RootURLController::config()->get('default_homepage_link');
            if (!SiteTree::get_by_link($defaultHomepage)) {
                $homepage = new HomePage();
                $homepage->Title = _t(__CLASS__.'.DEFAULTHOMETITLE', 'Home');
                $homepage->Content = _t(__CLASS__.'.DEFAULTHOMECONTENT', '<p>Welcome to SilverStripe! This is the default homepage. You can edit this page by opening <a href="admin/">the CMS</a>.</p><p>You can now access the <a href="http://docs.silverstripe.org">developer documentation</a>, or begin the <a href="http://www.silverstripe.org/learn/lessons">SilverStripe lessons</a>.</p>');
                $homepage->URLSegment = $defaultHomepage;
                $homepage->Sort = 1;
                $homepage->write();
                $homepage->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
                $homepage->flushCache();
                DB::alteration_message('Home page created', 'created');
            }
        }
    }
}
