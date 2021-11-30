# Virtual Y 1on1 Meeting

This module provides a base implementation of Virtual Y 1on1 Meetings.

![Virtual Y 1on1 meeting](assets/vy_14_virtual_meeting.png "Virtual Y 1on1 Meeting Demo")
<!--Short video demo: https://www.loom.com/share/c49ef7bb4b2a4435ac78bfc53582e2f1-->

## Requirements

This module requires:

- [State Machine](drupal.org/project/state_machine)
- [Date Recur](https://www.drupal.org/project/date_recur)
- [Date Recur Modular](https://www.drupal.org/project/date_recur_modular)

These are all required by [openy_gated_content/composer.json](../../composer.json) and are installed automatically if you've installed this module using `composer`.

## Module Structure

This module consists of the 1on1 Meeting entity, additional logic that works with it, and additional modules for the Virtual Y application.

The 1on1 Meeting entity is a connection between the Virtual Y User, Trainer, and time for the virtual meeting. Every meeting that is associated with a client and trainer is shown on their Virtual Y calendar in the app.

You cannot use this module without SSL on your website as it is required by webRTC.

### 1on1 Meeting Series

You can create a training series for your clients using the Date Recurrency field. If you change recurrence patterns, the module will automatically update all 1on1 Meeting instances in your series.

### 1on1 Meeting Entity

| Field name | Field machine name | Field type | Description |
| ---------- | ----------- | ----------- | ----------- |
| Customer   | customer_id | entity reference | The Drupal user ID of the client of the 1on1 Meeting entity. |
| Instructor | instructor_id | entity reference | The Drupal user ID of the instructor of the 1on1 Meeting entity. |
| Customer metadata | customer_metadata | text_long | The metadata for the customer from the CRM system (for example user id, email, or something else). |
| Instructor metadata | instructor_metadata | text_long | The metadata for the instructor from the CRM system (for example user id, email, or something else). |
| Training type | training_type | list_string | One to one call on the Virtual YMCA platform or link to a remote platform (Zoom, Meet, etc). Available options: `1-1` and `link` |
| Remote Link | remote_link | link | The link to a remote platform (Zoom, Meet, etc). Used when Training type is `link`. |
| Training Date | date | daterange | Training start and end time. Stores the date in an ISO format `YYYY-MM-DDTHH:MM:SS` in `value` and `end_value` fields. |
| Created | created | created | The time that the Training was created. |
| Changed | changed | changed | The time that the Training was last edited. |

### PersonalTrainingProvider plugin

This module implements `PersonalTrainingProvider` annotation. You can find an example of the plugin implementation in `src/Plugin/PersonalTrainingProvider`. Plugin should contain annotation with `id`, `label` and `config` and implement `PersonalTrainingProviderInterface` with `checkPersonalTrainingAccess` and `getUserPersonalTrainings` methods.

### 3rd Party System Data

To save data from third-party systems to PTF you should create a custom module with a
`PersonalTrainingProvider` plugin instance and implement sync tool for fetching data.

## Server Configuration

The 1on1 Virtual Meetings module has two modes: `native` and `remote link`.

![Virtual Y 1on1 meeting](assets/vy_14_remote_link.png "Virtual Y 1on1 Meeting type demo")

Using `remote link` mode you can use a remote link to your existing meeting application like Zoom, Meet, Teams, etc. Any solution that allows direct links to meetings can be used.

Using `native` mode you can organize 1-1 calls inside the Virtual Y app using WebRTC technology.

To enable the 1on1 Virtual Meetings module, you have to set up 3 additional services—either on self-hosted infrastructure or somewhere on the internet:

- `Signaling server` - A lightweight JavaScript app that negotiates the connection between the instructor and member. The Virtual Y team has developed [the Virtual Y Signaling Server](https://github.com/open-y-subprojects/virtual_y_signaling_server) to extend the [simple-peer library](https://github.com/feross/simple-peer/). Follow the [instructions in the readme](https://github.com/open-y-subprojects/virtual_y_signaling_server#signaling-server-for-virtual-y) to install the signaling server.
- `STUN server` - Google runs STUN servers as a free service, but with no SLA. They come preconfigured and should be reliable enough to use for most Virtual Y sites.
- `TURN server` - A `TURN` server is required to enable network address translation for WebRTC clients across the public internet. We recommend [installing `coTURN`](https://nextcloud-talk.readthedocs.io/en/latest/TURN/#install-and-setup-coturn-as-turn-server) on your own infrastructure for production. Please do not use public servers as they are not reliable.

![Virtual Y webrtc](https://user-images.githubusercontent.com/238201/128903503-f27e2eb9-fc06-4073-8457-bf53d82415c7.png)

## Debugging

1on1 Virtual Meetings has a debugger that shows all connection information that could help in troubleshooting.

Just set Debug (at this form: `admin/virtual-y/personal_training/settings`) to any number more than 0 and check your browser console.

### Common Issues

#### TURN Server Certificate Expired

If users are unable to connect and only receive a "Connecting..." message, it's possible the `TURN` server certificate needs to be renewed.

First, try restarting the server to see if the issue is resolved. Then renew the cert:

- ssh user@coturn.example.com
- certbot renew --cert-name coturn.example.com
- screen -R
- cd /root/simple-peer/perf
- node server.js
- ctrl + a + d OR close the tab

## Session Cancellation Notifications

This module allows sending cancel session notification messages to the client referenced by the `customer_id` field on the 1on1 Meeting entity. Cancellations are sent when the admin user changes the state of the 1on1 Meeting entity or 1on1 Meeting series to `Cancelled`.

**Please check your mail system before using this feature.**

This module uses the default Drupal mail sending functions to send emails.

You can enable this feature or change the message template at the 1on1 Meeting settings form: `/admin/virtual-y/personal_training/settings`.
