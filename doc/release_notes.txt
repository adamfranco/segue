
Segue v. 2.0-beta-22 (2008-06-09)
=================================

What is Segue?
------------------
Segue is an open source collaborative content management system designed for
e-learning that combines the ease of use of course management systems with the
flexibility of weblogs for creating various types of sites including course, news,
and journal.

This Segue 2.0 beta is capable of most of the site creation and editing needs of
users. We are continually adding features to bring Segue 2 up to feature parity with
Segue 1.x. See the Feature Request Tracker for a list of features that still need to
be completed: https://sourceforge.net/tracker/?group_id=82171&atid=565237


Current Version Notes
---------------------



Downloads
---------------------
For the latest and archived versions, please download from SourceForge.

http://sourceforge.net/project/showfiles.php?group_id=82171&package_id=246956


Documentation
---------------------
Segue includes contextual help for users. Additional documentation can be found
online at:

http://sourceforge.net/project/showfiles.php?group_id=82171&package_id=246956


Installation
---------------------
See the INSTALL.txt file in the Segue root directory for installation instructions
or read on the web at:

http://sourceforge.net/project/showfiles.php?group_id=82171&package_id=246956


Bug Tracker
---------------------
http://sourceforge.net/tracker/?group_id=82171&atid=565234







===================================================================
| Prior Segue Release Notes
| (See the Segue change log for more details)
===================================================================


v. 2.0-beta-22 (2008-06-09)
----------------------------------------------------




v. 2.0-beta-21.3 (2008-05-23)
----------------------------------------------------
This release fixes a few small issues found in 2.0 beta 21.2.

This release fixes a few errors affected a few users, notably a work-around for a
PHP/PDO bug that results in segmentation faults when escaped quotes exist in an
SQLstring that is then prepared. This is occurring when checking authorization for
users who are members of groups that have a quote in their LDAP DN.

Missing theme images now do not fill the logs with errors.

Upgrades from versions prior to beta 21 require running the appropriate
updaterlocated under Admin Tools --> Segue Updates.



v. 2.0-beta-21.2 (2008-05-22)
----------------------------------------------------
This release fixes a few small issues found in 2.0 beta 21.1.

In addition to portal speed improvements, thumbnail images now work again.

Upgrades from versions prior to beta 21 require running the appropriate
updaterlocated under Admin Tools --> Segue Updates.



v. 2.0-beta-21.1 (2008-05-20)
----------------------------------------------------
This release of Segue adds theming choices for sites. Site-editors can now choose
between a number of built-in themes, each of which supports a number of options for
changing color scheme and/or fonts.

Additionally, local copies of any theme can be created for a site. These copies
enable site-editors to modify the CSS and HTML templates that define a theme,
enabling full customization for users familiar with CSS and HTML. In a future
release we will eventually add a public gallery of themes that users will be able to
submit their themes to and choose themes for their sites from.

New themes in this release: Rounded Corners, Shadow Box, Tabs.

Usage of this new theming system requires running the appropriate updater located
under Admin Tools --> Segue Updates.



v. 2.0-beta-21 (2008-05-20)
----------------------------------------------------
This release of Segue adds theming choices for sites. Site-editors can now choose
between a number of built-in themes, each of which supports a number of options for
changing color scheme and/or fonts.

Additionally, local copies of any theme can be created for a site. These copies
enable site-editors to modify the CSS and HTML templates that define a theme,
enabling full customization for users familiar with CSS and HTML. In a future
release we will eventually add a public gallery of themes that users will be able to
submit their themes to and choose themes for their sites from.

New themes in this release: Rounded Corners, Shadow Box, Tabs.

Usage of this new theming system requires running the appropriate updater located
under Admin Tools --> Segue Updates.



v. 2.0-beta-20.1 (2008-05-05)
----------------------------------------------------
This release of Segue 2.0 includes dramatic speed improvements over beta 19.1 due to
a new implementation of Harmoni's Authorization and Hierarchy system. Usage of this
new systems requires running the appropriate updater located under Admin Tools -->
Segue Updates. This updater will take several minutes to run and will make
irrevocable changes to your database. Please back up your database before running
this updater.

Other improvements to Segue include support for migrating tags from Segue 1 sites,
and an improved installation process.

Segue 2.0 beta 20 uses Polyphony 1.1.0 and Harmoni 1.2.0.



v. 2.0-beta-20 (2008-05-05)
----------------------------------------------------
This release of Segue 2.0 includes dramatic speed improvements over beta 19.1 due to
a new implementation of Harmoni's Authorization and Hierarchy system. Usage of this
new systems requires running the appropriate updater located under Admin Tools -->
Segue Updates. This updater will take several minutes to run and will make
irrevocable changes to your database. Please back up your database before running
this updater.

Other improvements to Segue include support for migrating tags from Segue 1 sites,
and an improved installation process.

Segue 2.0 beta 20 uses Polyphony 1.1.0 and Harmoni 1.2.0.



v. 2.0-beta-19.1 (2008-04-13)
----------------------------------------------------




v. 2.0-beta-19 (2008-04-11)
----------------------------------------------------
This release fixes a number of bugs and adds the ability to tag content.

Notable user-facing changes:

 * Tagging system.

 * Minor speed improvements to the portal

 * Refined Migration UI.

 * Role-Setting now works.

 * Migration tools now work with more sites.

Notable back-end changes:

 * New Harmoni_Db system allows for usage of prepared statements for improved
database performance.

Segue 2.0 beta 19 uses Polyphony 1.1.0 and Harmoni 1.1.0.



v. 2.0-beta-18 (2008-04-03)
----------------------------------------------------
This release fixes a number of bugs and adds a new Portal display.

Notable user-facing changes:

 * Portal 'Folders' to break up the lists of sites

 * Major speed improvements to the portal

 * Comment authors are now preserved when migrating from Segue 1

 * URLs to elsewhere in Segue now work more reliably when migrating from Segue 1.

Segue 2.0 beta 17 uses Polyphony 1.0.6 and Harmoni 1.0.6.



v. 2.0-beta-17 (2008-03-31)
----------------------------------------------------
This release fixes a number of bugs to avoid errors in RSS feeds, Wiki-Text in
comments, and the site map. As well, short-form URLs now work with placeholder names
that contain hyphens and under-scores.

Segue 2.0 beta 17 uses Polyphony 1.0.5 and Harmoni 1.0.5.



v. 2.0-beta-16 (2008-03-26)
----------------------------------------------------
This release adds Media Library quotas and enables redirects from Segue 1 instances.

Segue 2.0 beta 16 uses Polyphony 1.0.5 and Harmoni 1.0.5.



v. 2.0-beta-15.1 (2008-03-25)
----------------------------------------------------
This release fixes a little bug introduce in beta 15.



v. 2.0-beta-15 (2008-03-25)
----------------------------------------------------
The Segue 2.0 beta is capable of most of the site creation and editing needs of
users. We are continually adding features to bring Segue 2 up to feature parity with
Segue 1.x. See the Feature Request Tracker for a list of features that still need to
be completed: https://sourceforge.net/tracker/?group_id=82171&atid=565237 This
release adds a number of major improvements as well as fixes a large number of bugs.
Notable user-facing changes: 

 * Migration tools for importing Segue 1 sites

 * New plugins for Breadcrumbs and RSS links

 * Improvements to UI2, 'New Mode' editing interface to reduce visual clutter.

 * New Site-Map view.

 * New version of FCK editor, now supports Safari.

Upgrade Notes: For upgrades from releases prior to beta 15, please run the updates
located under Admin Tools --> Segue Updates. 

Segue 2.0 beta 15 uses Polyphony 1.0.4 and Harmoni 1.0.5.



v. 2.0-beta-14 (2008-03-10)
----------------------------------------------------
This release fixes a few bugs in the permissions UI and fixes some HTML validation errors.

Notable Changes:

 * Permissions are more reliable cross-browser

 * File downloads now work in IE

 * Download Plugin description now updates immediately after change.



v. 2.0-beta-13 (2008-03-03)
----------------------------------------------------
This release fixes a number of Javascript bugs that were preventing some site
editing and commenting actions from working for users of Safari and InternetExplorer.

Also in this release are some improvements to the logging system that allow
filtering of some errors (such as from web-crawlers) and the graphing of Segue usage statistics.

See the change log for details on further fixes and improvements.

Segue 2.0 beta 13 uses Harmoni 1.0.2 and Polyphony 1.0.1.



v. 2.0-beta-12 (2008-02-21)
----------------------------------------------------
This Segue beta release fixes a number of issues discovered in testing the previous
beta release.

Notable Improvements: Clean installs now work. Comments and MediaLibrary now work
in InternetExplorer. Rich-Text Editor now uses the custom configuration properly.
Invalid requests now generate HTTP error codes to indicate to web-crawlers that
their request is invalid. Error pages are now friendlier. Cookie values are no
longer added to URLs.

Segue 2.0 beta 12 uses Harmoni 1.0.1 and Polyphony 1.0.1.



v. 2.0-beta-11 (2008-02-18)
----------------------------------------------------
This release fixes a few bugs that were noticed in testing beta 10.



v. 2.0-beta-10 (2008-02-15)
----------------------------------------------------
This release includes fixes to a large number of bugs. In addition to a few new
changes and improvements.

Notable user-facing changes: Media library should be much more stable. Authors and
editors now always get links to edit-mode. Now can reliably use path_info-based urls
as the primarily URL scheme. Admin-only export/import tools to allow migration of sites.

Notable back-end changes: Updated to Harmoni 1.0.0. Internal links are now parsed
and tokenized to improve mobility of the content.

Segue 2.0 beta 10 uses Harmoni 1.0.0 and Polyphony 1.0.0.



v. 2.0-beta-9 (2008-01-15)
----------------------------------------------------
This release fixes a number of bugs in the Roles user interface. See the change-log
for details.



v. 2.0-beta-8 (2008-01-14)
----------------------------------------------------
This release clarifies the role of slots/site-placeholders and brings some
performance improvements to the portal page, especially for users with large numbers
of course sites.

This version also adds versioning support for content blocks and programatic
sorting of content to enable blogging.

Upgrade Notes: The icons/ directory has been renamed to images/ to prevent
conflicts with the Apache system icons directory. Be sure to run the Segue Updaters
under Admin tools to update the table structure to support versioning.

Segue 2.0 beta 8 uses Polyphony 0.10.7 and Harmoni 0.13.7.



v. 2.0-beta-7 (2007-12-20)
----------------------------------------------------
This release fixes a large number of bugs as well as improves the validity of the
XHTML markup generated by Segue.



v. 2.0.0-beta-6 (2007-12-12)
----------------------------------------------------
This release fixes a number of bugs as well as provides new admin-tools for the
management of slots (site-placeholders). Additionally, security measures have been
put in place to prevent cross-site-scripting (XSS) attacks.

For upgrades from earlier releases, please run the update located under Admin Tools
--> Segue Updates.

This release of Segue uses Harmoni 0.13.5 and Polyphony 0.10.5.



v. 2.0.0-beta-5 (2007-11-29)
----------------------------------------------------
This release includes a new hierarchical authorization-setting user interface that
allows for the designation of roles to any user or group anywhere in the site hierarchy.

This release uses Harmoni 0.13.4 and Polyphony 0.10.4.



v. 2.0.0-beta-4 (2007-11-13)
----------------------------------------------------
This release fixes an installer bug that was preventing fresh installs from
completing. It also fixes some ArrangeMode bugs that were allowing Menus and
MenuItems to be dragged to invalid places.

This release uses Harmoni 0.13.4 and Polyphony 0.10.3.



v. 2.0.0-beta-3 (2007-11-09)
----------------------------------------------------
This release fixes a large number of bugs and adds role-based authorization-setting
user interfaces. As well, the Plugin System had its interface hierarchy reworked.

The major Harmoni improvement is the ability to attach externally-defined groups
(such as from LDAP) underneath locally defined groups.

Segue 2.0.0-beta-3 runs on Harmoni 0.13.3 and Polyphony 0.10.3.

Changes to the Agent tables require running a database updater script:
segue/main/harmoni/core/DBHandler/db_updater.php 



v. 2.0.0-beta-2 (2007-09-25)
----------------------------------------------------




v. 2.0.0-beta-1 (2007-09-20)
----------------------------------------------------




