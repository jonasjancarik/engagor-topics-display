# Engagor topics display
Displays all Engagor (CX Social) topics the authenticated user has access to in a simple HTML table and optionally outputs an XLS file. Useful for Engagor users/admins managing a high number of monitored topics and keywords.

## What problem does this solve?

The Engagor UI (https://app.engagor.com/account/XXXX/editTopics) can be limiting for power users since it doesn't allow for a quick overview/search of all the monitored keywords - this can lead to duplicates where keywords are unnecessarily monitored under multiple topics.

## How to use?

1) Create a config.php file in the root directory with your credentials - the account ID and API access token (see docs https://developers.engagor.com/documentation/steps/)

2) In browser, navigate to index.php (e.g. http://localhost/engagor-topics-display/index.php)

3) For .xls output, use index.php?format=xls (e.g. http://localhost/engagor-topics-display/index.php?format=xls). Note that the XLS file is basically the same simple HTML table - Excel will show a warning message upon opening that the format doesn't match.
