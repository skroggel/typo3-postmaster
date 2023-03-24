# Postmaster
Extension for mass email sending.

## Features
- Time-delayed sending
- Opening rate, click rate & bounce rate via BE module
- Automatic detection of hard bounces
- Personalized mass mailing
- Optimized to reduce process load and database space usage
- Fully compatible with fluid-templates
- Numerous ViewHelpers
- General API for use in own extensions
- Included List-Unsubscribe-Header
- Attachments possible
- Elaborated caching-framework for high performance
- Multi-Part E-Mails (Plaintext / HTML)

## Usage in your own extension
The extension has a Mailservice and several ViewHelpers that can be used in your extension.

You can find an example for the usage of the Mailservice in the Example-Folder.
There you can also find an example for the usage of the ViewHelpers in the corresponding e-mail templates.

## Setup
1. Include the TypoScript in your root-page
2. Setup the CLI-commands in the scheduler

## CLI-Commands

### postmaster:send
This command is responsible for sending all of your e-mails.
Parameters:
- emailsPerJob -> How many queue-jobs are to be processed during one command-call
- emailsPerInterval -> How may emails are to be sent at maximum for each queue-job
- settingsPid -> Pid to fetch TypoScript-settings from',
- sleep -> How many seconds the script should sleep after each e-mail sent

### postmaster:analyseStatistics
This script analyses the statistics for sent e-mails
Parameter:
- daysAfterSendingStarted -> Defines how long after sending has been started the statistics should be updated (default: 30 days)

## postmaster: cleanup
Deleted old e-mails with or without the corresponding statistics
Parameters:
- daysAfterSendingFinished -> Defines how many days after its sending has been finished an queueMail and their corresponding data will be deleted (default: 30 days)
- types -> Defines which types of mails the cleanup should look for (comma-separated) (Default: only type "0")
- includingStatistics -> Defines whether the statistics should be deleted too (Default: 0)

## postmaster:analyseBounceMails
Processed the bounced mails and puts their response-data into the database.

**NOTE: to be able to process bounce-mails you have to use a POP3/IMAP-account!**

Parameters:
- username -> The username for the bounce-mail account
- password ->The password for the bounce-mail account
- host -> The host of the bounce-mail account
- port -> The port for the bounce-mail account (default: 143 - IMAP)
- usePop3 -> Use POP3-Protocol instead of IMAP (default: 0)
- tlsMode -> The connection mode for the bounce-mail account (none, tls, notls, ssl, etc.; default: notls)
- inboxName -> The name of the inbox (default: INBOX)
- deleteBefore -> If set, all mails before the given date will be deleted (format: yyyy-mm-dd)
- maxEmails -> Maximum number of emails to be processed (Default: 100)

## postmaster:processBounceMails
Processes the fetched bounced-mails by their response-codes and sets the status of the corresponding recipients in the database
Parameter:
- maxEmails -> Maximum number of emails to be processed (Default: 100)

## ViewHelpers
### cache.renderCache
If you e.g. send a newsletter to large amount of recipients there may be a lot of content in your newsletter that is the same for each recipient. In that case you should cache the content in order to get a better performance.
You can also add an additional string to the cacheIdentifier e.g. in order to distinguish custom sortings and you can define non-cached markers.
Example:
```
<postmaster:cache.renderCache queueMail="{queueMail}" isPlaintext="true" additionalIdentifier="mySorting">
    CONTENT TO CACHE
</postmaster:cache.renderCache>
```
### email.replace.redirectLinks
Replaces all link with a redirect link in order to track them for the statistics
Example:
```
<postmaster:email.replace.redirectLinks queueMail="{queueMail}" isPlaintext="true">
    Testen Sie hier: http://www.google.de
    Testen Sie da: [http://www.yahoo.de/#anchor-1]
    Testen Sie dort: mail@example.com
    Testen Sie überall: #anchor-2
</postmaster:email.replace.redirectLinks>

<postmaster:email.replace.redirectLinks queueMail="{queueMail}" >
    <p>
        <a href="http://www.google.de">Testen Sie hier</a>
        <a href="http://www.yahoo.de/#anchor-1">Testen Sie da</a>
        <a href="mailto:mail@example.com">Testen Sie dort</a>
        <a href="#anchor-2">Testen Sie überall</a>
    </p>
</postmaster:email.replace.redirectLinks>
```
Result:
```
    Testen Sie hier: http://www.example.com/umleitung/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.google.de
    Testen Sie da: [http://www.example.com/umleitung/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.yahoo.de%2F%23anchor-1
    Testen Sie dort: mail@example.com
    Testen Sie überall: #anchor-2

    <p>
        <a href="http://www.example.com/umleitung/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.google.de">Testen Sie hier</a>
        <a href="http://www.example.com/umleitung/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.yahoo.de%2F%23anchor-1">Testen Sie da</a>
        <a href="mailto:mail@example.com">Testen Sie dort</a>
        <a href="#anchor-2">Testen Sie überall</a>
    </p>
```

### email.replace.rteLinks
If you use a text from a backend-richtext-editor you may want to replace the RTE-Links to real ones.
This ViewHelper does the job for you. Works with old and new RTE-links.
Example:
```
<postmaster:email.replace.rteLinks isPlaintext="true">
    Testen Sie hier <link https://www.google.de _blank external-link Titel ist Titel>mit Klick</link>!
    Testen Sie da <link http://www.yahoo.de/#anchor-1 _blank external-link "Titel ist Titel">mit Klick</link>!
    Testen Sie dort <link mail@example.com _blank mail "Titel ist Titel">mit Klick</link>!
    Testen Sie überall <link 9999 _blank tinternal-link "Titel ist Titel">mit Klick</link>!
    Testen Sie hüben <link 9999#anchor-1 _blank tinternal-link "Titel ist Titel">mit Klick</link>!
    Testen Sie drüben <link file:999 _blank download "Titel ist Titel">mit Klick</link>!

    Testen Sie hier <a class="external-link" href="https://www.google.de" target="_blank" title="Test">mit Klick</a>!
    Testen Sie da <a class="external-link" href="http://www.yahoo.de/#anchor-1" target="_blank" title="Test">mit Klick</a>!
    Testen Sie dort <a class="mail" href="mailto:mail@example.com" title="Test">mit Klick</a>!
    Testen Sie überall <a class="internal-link" href="t3://page?uid=9999" title="Test">mit Klick</a>!
    Testen Sie hüben <a class="internal-link" href="t3://page?uid=9999#anchor-1" title="Test">mit Klick</a>!
    Testen Sie drüben <a class="download" href="t3://file?uid=999" title="Test">mit Klick</a>!

</postmaster:email.replace.rteLinks>

<postmaster:email.replace.rteLinks>
    <p>
        Testen Sie hier <link http://www.google.de _blank external-link "Titel ist Titel">mit Klick</link>!<br />
        Testen Sie da <link http://www.yahoo.de/#anchor-1 _blank external-link "Titel ist Titel">mit Klick</link>!<br />
        Testen Sie dort <link mail@example.com _blank mail "Titel ist Titel">mit Klick</link>!<br />
        Testen Sie überall <link 9999 _blank internal-link "Titel ist Titel">mit Klick</link>!<br />
        Testen Sie hüben <link 9999#anchor-1 _blank internal-link "Titel ist Titel">mit Klick</link>!<br />
        Testen Sie drüben <link file:999 _blank download ">Titel ist Titel"mit Klick</link>!
    </p>
    <p>
        Testen Sie hier <a class="external-link" href="https://www.google.de" target="_blank" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie da <a class="external-link" href="http://www.yahoo.de/#anchor-1" target="_blank" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie dort <a class="mail" href="mailto:mail@example.com" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie überall <a class="internal-link" href="t3://page?uid=9999" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie hüben <a class="internal-link" href="t3://page?uid=9999#anchor-1" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie drüben <a class="download" href="t3://file?uid=999" target="_blank" title="Titel ist Titel" style="color:red;">mit Klick</a>!
    </p>
</postmaster:email.replace.rteLinks>
```

Result:
```
    Testen Sie hier mit Klick [https://www.google.de]!
    Testen Sie da mit Klick [http://www.yahoo.de/#anchor-1]!
    Testen Sie dort mit Klick [mailto:mail@example.com]!
    Testen Sie überall mit Klick [http://www.example.com/testseite]!
    Testen Sie hüben mit Klick [http://www.example.com/testseite#anchor-1]!
    Testen Sie drüben mit Klick [http://www.example.com/fileadmin/test.pdf]!

    Testen Sie hier mit Klick [https://www.google.de]!
    Testen Sie da mit Klick [http://www.yahoo.de/#anchor-1]!
    Testen Sie dort mit Klick [mailto:mail@example.com]!
    Testen Sie überall mit Klick [http://www.example.com/testseite]!
    Testen Sie hüben mit Klick [http://www.example.com/testseite#anchor-1]!
    Testen Sie drüben mit Klick [http://www.example.com/fileadmin/test.pdf]!

    <p>
        Testen Sie hier <a href="http://www.google.de" title="Titel ist Titel" target="_blank" class="external-link">mit Klick</a>!<br />
        Testen Sie da <a href="http://www.yahoo.de/#anchor-1" title="Titel ist Titel" target="_blank" class="external-link">mit Klick</a>!<br />
        Testen Sie dort <a href="mailto:mail@example.com" title="Titel ist Titel" target="_blank" class="mail">mit Klick</a>!<br />
        Testen Sie überall <a href="http://www.example.com/testseite" title="Titel ist Titel" target="_blank" class="internal-link">mit Klick</a>!<br />
        Testen Sie hüben <a href="http://www.example.com/testseite#anchor-1" title="Titel ist Titel" target="_blank" class="internal-link">mit Klick</a>!<br />
        Testen Sie drüben <a href="http://www.example.com/fileadmin/test.pdf" target="_blank" class="download">Titel ist Titel"mit Klick</a>!
    </p>
    <p>
        Testen Sie hier <a href="https://www.google.de" class="external-link" target="_blank" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie da <a href="http://www.yahoo.de/#anchor-1" class="external-link" target="_blank" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie dort <a href="mailto:mail@example.com" class="mail" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie überall <a href="http://www.example.com/testseite" class="internal-link" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie hüben <a href="http://www.example.com/testseite#anchor-1" class="internal-link" title="Titel ist Titel" style="color:red;">mit Klick</a>!<br />
        Testen Sie drüben <a href="http://www.example.com/fileadmin/test.pdf" class="download" target="_blank" title="Titel ist Titel" style="color:red;">mit Klick</a>!
    </p>
```

### email.uri.action / email.uri.page / email.uri.typolink
Use this ViewHelpers to generate valid absolute links when sending emails.
This is important because mails are sent via CLI.

### email.image
Renders an image when sending e-mails.
This is important because mails are sent via CLI.

Example:
```
<postmaster:email.image src="EXT:postmaster/Resources/Public/Images/logo.png" width="536" height="200c"  />
```

### email.pixelcounter
This ViewHelper adds a counter pixel to your email in order to be able to track whether it has been opened.
Opening can be tracked by email, recipient or both.

```
<postmaster:email.pixelCounter queueMail="{queueMail}" />
```

### email.plaintextLineBreaks

When defining the plaintext-part of your multi-part-emails using template-files it can be very annoying that
every indent or linebreak you use for improving the readability of your template is also shown in your plaintext-part of the final email.

This ViewHelper solves that problem by removing all linebreaks and indents. To add a linebreak manually, just use ``\n``
Example:

```
<postmaster:email.plaintextLineBreaks>With line break,
    remove line break and indent and\nadd manual line break.</postmaster:email.plaintextLineBreaks>
```

Result:
```
With line break, remove line break and indent and
add manual line break.
```

### email.recipientSalutation
This ViewHelper generated a proper salutation for the recipients of your email.

Example:
```
<postmaster:email.recipientSalutation queueRecipient="{queueRecipient}" prependText="Hello " appendText=" M. Sc." fallbackText="Hello" />
```

Result:
```
Hello Mr. Dr. Schmidt M. Sc.
```

## When migrating from rkw_mailer to postmaster
Execute the following MySQL-queries BEFORE checking the database-tables and fields via backend!
```
RENAME TABLE `tx_rkwmailer_domain_model_queuemail` TO `tx_postmaster_domain_model_queuemail`;
RENAME TABLE `tx_rkwmailer_domain_model_queuerecipient` TO `tx_postmaster_domain_model_queuerecipient`;
RENAME TABLE `tx_rkwmailer_domain_model_mailingstatistics` TO `tx_postmaster_domain_model_mailingstatistics`;
RENAME TABLE `tx_rkwmailer_domain_model_clickstatistics` TO `tx_postmaster_domain_model_clickstatistics`;
RENAME TABLE `tx_rkwmailer_domain_model_openingstatistics` TO `tx_postmaster_domain_model_openingstatistics`;
RENAME TABLE `tx_rkwmailer_domain_model_bouncemail` TO `tx_postmaster_domain_model_bouncemail`;
RENAME TABLE `tx_rkwmailer_domain_model_openingstatistics` TO `tx_postmaster_domain_model_openingstatistics`;
UPDATE tt_content SET list_type = "postmaster_tracking" WHERE list_type = "rkwmailer_rkwmailer";
```

