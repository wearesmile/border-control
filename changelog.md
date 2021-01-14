# CHANGELOG.md

## 1.0.5 (10-02-2020)

Fix:

  - Moved the saving of latest revision meta from transition_post_status hook to pre_post_update hook
  - As a result the functionality of sbc_publish_revision has been moved to sbc_save_post_revision_meta
  - Switched from wp_get_post_revisions to get_posts & removed the foreach loop, as we only need to grab the latest revision

Bugfixes:

  - Post meta is now only copied over from latest revision on publish, this resolves a bug where post meta would show as updated even if the post had not been approved.

## 1.0.51 (09-03-2020)

Bugfixes:

  - Added a conditional to sbc_manage_caps method to prevent errors if the editor role didn't exist.

## 1.0.52 (16-03-2020)

Bugfixes:

  - Added a Tertiary variable for the post title that is pulled through when we notify the owner of changes to a post. Previously you could recieve an email telling you that the post titled `auto draft` had been updated. Which wasn't particularly useful to the end user.

## 1.0.53 (01-04-2020)

Feature:

  - Users can now preview post meta changes
  - Users can now restore post meta changes from old revisions.

## 1.0.54 (01-04-2020)

Hotfix:

  - Added a function exists check for add meta function.

## 1.0.55 (13-01-2021)

Fix:

  - Fixed issue where posts would 404 if it was submitted for review, but was published before border control was active.