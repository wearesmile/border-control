# About Border Control

Border Control is a WordPress plugin by [SMILE](https://wearesmile.com/) which allows you to leverage a workflow system for the creation and management of content across your organisation.

## Workflow

Running a website can sometimes require distributing the load of content management amongst multiple people. But governing this can be tricky: If you turn a blind eye, then very quickly, your website will turn into a wild west where content is up for grabs by anyone.

Border Control takes care of this for you, giving you the confidence to delegate work across your team, safe in the knowledge that your staff are able to operate within the scope of their individual remits. This plugin enables a site to limit publishing of any post type to either a specific group of users and/or a set of user roles. You can also be more granular and limit the moderator on a post by post basis.

Users without the publish capablity will require a moderator to review and publish their post. 

## Edit behind the scenes

In stock WordPress, you can only update a published post. Converting it to a draft takes it offline. With Border Control, post editors can make changes to your post, and save them as a draft or send for review, without taking the current version offline.

# Quick Start Guide

Settings are edited at `/wp-admin/options-general.php?page=border_control`

## Identifying post moderators

On the settings page you can set who will moderate posts, and additionally define which post types are subject to moderation via Border Control.

Anybody who is not listed directly or via their role in these settings will not have permission to publish any of the post types which have been selected here, regardless of their role permissions.

## Publishing a post

Border Control adds a meta box to the post interface where users can select a moderator to review their post.

### If you are a moderator
- You can instantly publish any post you make
- You can approve posts which have no defined moderators
- You can approve posts which have you in the defined moderators
- You will recieve notifications for posts which you will be able to moderate

### If you are not a moderator
- You can not instantly publish any post you make, it must be reviewed
- You must assign a moderator from the list on the post edit page in the upper right before it goes to approval
- Changes to a post (previously aprroved or not) would require moderation again

# Contributing
Please read CONTRIBUTING.md for details on our code of conduct, and the process for submitting pull requests to us.
