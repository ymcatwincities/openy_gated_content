# Virtual Y Logs module

This module adds ability to track and save user activity for every Virtual Y user.
It tracks next items:
- userLoggedIn
- entityView
- videoPlaybackStarted
- videoPlaybackEnded
- sessionTime

# Known issues

Virtual Y logs best works with the latest Open Y (which has latest CSV Encoder).
If you faced with a fatal error during attempt to export logs, please revert this change:
https://github.com/ymcatwincities/openy_gated_content/commit/33f3bd8eec0f5a9927c55931b1b0dd7f26f49ccf#diff-2e89b91ef29ae86e4bd7de40053c36a84d761929ce86b9ad9c13b48243e90716
