# Border Control

This plugin enables a site to limit publishing of any post type to either a specific group of users and/or a set of user roles. You can also be more granular and limit the moderator on a post by post basis.

## Using Border Control

- Settings are edited at `/wp-admin/options-general.php?page=border_control`

### Identifying moderators

On the settings page oyu can set global post moderators and define which post types you need moderating.

Anybody who is not listed directly or via their role in these settings will not have permission to publish any of the post types which have been selected here, regardless of their role permissions.

### Publishing a post

#### If you are a moderator
- You can instantly publish any post you make
- You can approve posts which have no defined moderators
- You can approve posts which have you in the defined moderators
- You will recieve notifications for posts which you will be able to moderate

#### If you are not a moderator
- You can not instantly publish any post you make, it must be reviewed
- You must assign a moderator from the list on the post edit page in the upper right before it goes to approval
- Changes to a post (previously aprroved or not) would require moderation again