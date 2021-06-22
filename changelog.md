# Change Log
All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).



## Partial Gutenberg Compatibility - [1.1.0] [Minor]

**Features**
  - *Partial Gutenberg support, some classic editor features may not work as intended including but not limited to multi-moderator functionality*

**Changes**
  - *Changelog has been reformatted, from 1.1.0 versioning will adhere to [Semantic Versioning](http://semver.org/).*

**Release Date:** 22/06/2021 |
**Release Author:** James Glendenning



## Bugfixes - [1.0.55] [Patch]

**BugFixes**
  - *Fixed issue where posts would 404 if it was submitted for review, but was published before border control was active.*

**Release Date:** 13/01/2021 |
**Release Author:** James Glendenning



## Hotfix - [1.0.54] [Patch]

**Hotfix**
  - *Added a function exists check for add meta function..*

**Release Date:** 01/04/2020 |
**Release Author:** James Glendenning



## Restore post meta changes - [1.0.53] [Patch]

**Features**
  - *Users can now preview post meta changes.*
  - *Users can now restore post meta changes from old revisions.*

**Release Date:** 01/04/2020 |
**Release Author:** James Glendenning



## Bugfixes - [1.0.52] [Patch]

**Bugfixes**
  - *Added a Tertiary variable for the post title that is pulled through when we notify the owner of changes to a post. Previously you could recieve an email telling you that the post titled `auto draft` had been updated. Which wasn't particularly useful to the end user.*

**Release Date:** 16/03/2020 |
**Release Author:** James Glendenning



## Bugfixes - [1.0.51] [Patch]

**Bugfixes**
  - *Added a conditional to sbc_manage_caps method to prevent errors if the editor role didn't exist.*

**Release Date:** 09/03/2020 |
**Release Author:** James Glendenning



## Bugfixes - [1.0.50] [Patch]

**Bugfixes**
  - *Moved the saving of latest revision meta from transition_post_status hook to pre_post_update hook*
- *As a result the functionality of sbc_publish_revision has been moved to sbc_save_post_revision_meta*
- *Switched from wp_get_post_revisions to get_posts & removed the foreach loop, as we only need to grab the latest revision*
- *Post meta is now only copied over from latest revision on publish, this resolves a bug where post meta would show as updated even if the post had not been approved.*

**Release Date:** 10/02/2020 |
**Release Author:** James Glendenning


*Changes before 1.0.50 have been lost to the void of time...*