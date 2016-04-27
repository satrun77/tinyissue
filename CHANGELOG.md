# Tiny Issue Change Log

## Tiny Issue v2.6.0

- Redesign the Kanban view.
- Feature: Admin to prevent users from logging in. Make users Inactive or Blocked.
- Improved the menu items.
- Redesign all of the forms for adding or editing issues to be more consistent and user friendly.
- Tags management simplified to 3 standard "unchangeable" groups (Types, Status, & Resolution).
- Feature: added support for german translation. Thanks to @nickbe
- Stop links from leaving full screen mode on iOS devices.
- Bug fixes and other minor improvements.

## Tiny Issue v2.5.1

- Bug fixes

## Tiny Issue v2.5.0

- Feature: project Kanban board
- Feature: configurable date format

## Tiny Issue v2.4.0

- Compatible with PHP 5.5, PHP 5.6, & PHP 7
- Minor improvements & bug fixes
- The ability to set projects as private or not. Based on https://github.com/mikelbring/tinyissue/pull/173

## Tiny Issue v2.3.0

- Bug fixes
- Generic form to add new issue from dashboard

## Tiny Issue v2.2.0

- Support Laravel 5.1+
- Added smtp config to .env
- Style fixes

## Tiny Issue v2.1.2

- Bug fixes
- Responsive fixes

## Tiny Issue v2.1.1

- Bug fixes

## Tiny Issue v2.1.0

- Feature: Export project issues to CSV or XLS file
- Code quality changes
- Bug fixes

## Tiny Issue v2.0.0

- Migrate to Laravel 5
- Feature: Responsive design
- Feature: Sort and filter issues
- Feature: Add tags to issues
- Feature: Project progress bar
- Feature: Add/edit/delete project notes
- Feature: Add issues quote
- Feature: Convert issue no. text into the issue url
- Feature: Set default assignee for a project issues
- Feature: Move issues (including activities, comments, etc.) to another project

## Tiny Issue v1.3.1

- Bug fixes
- Small UI Changes
- Language fixes

### Upgrading from v1.3

- Replace the `app` folder

## Tiny Issue v1.3

- Added support for user language (issue#84)
- Login will take you back to where you wanted to go (issue#68)
- Minor CSS updates and bug fixes

### Upgrading from v1.2.3

- Run the `install/update_v1-1_3.sql` in your database
- Replace the `app` folder

## Tiny Issue v1.2.3

- Added support for SMTP encryption protocol
- Added Markdown support
- General bug fixes

### Upgrading from v1.2.2

- Replace the `app` folder

## Tiny Issue v1.2.2

- Added activity log to issue page
- Bug Fix: Assigning users to a project with no users, after creating the project
- Bug Fix: Admin stats and version

### Upgrading from v1.2.1

- Replace the `app` folder

## Tiny Issue v1.2.1

- Minor bug fixes
- Convert raw queries to query builder
- Added localization, now we have a language file for all text
- Added additional requirement checks in installer

### Upgrading from v1.2

- Replace the `app` folder

## Tiny Issue v1.2

- Feature: Requirement check on installation
- Feature: Added ability to edit a issue title / body
- Feature: Autolink URLs in comment and issue body
- Install: Will now check for installation when going to root by seeing if config.app.php exists
- Config: Added more mail settings to config
- Included .htaccess for mod-rewrite

### Upgrading from v1.1.1

- Replace the `app` folder

## Tiny Issue v1.1.1

- Bug fix: Your issue count was not returning the right value
- Bug fix: The activity view was not account for issues assigned to no one

### Upgrading from v1.1

- Replace the `app` folder

## Tiny Issue v1.1

- Upgraded Laravel 2.x to Laravel 3.1.4, should fix some bugs related to PHP 5.4
- Bug fix: Added a URL option in the config to specify your URL, should fix path bug on non-apache servers

### Upgrading from v1.0

- Move `app/assets/uploads` to `/uploads`
- Run the `install/update_v1-1_1.sql` in your database
- Replace the `app` folder
