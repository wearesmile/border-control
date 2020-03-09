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