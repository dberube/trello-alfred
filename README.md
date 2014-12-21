Trello Workflow for Alfred v2
=============================

![Trello Workflow for Alfred Icon](http://files.dtb.me.s3.amazonaws.com/public/trello-alfred/icon.png)

**Description:** Trello Workflow for the Mac OS app **Alfred v2** that *allows you to quickly create new cards* on your **Trello** board lists.

**Version:** 0.8

Installation & Setup
-------------------------------

-	Double click on the trello.alfredworkfow file to have Alfred v2 automatically install this workflow
-	Open Alfred and enter the trigger: **trello.auth**
	-	This will open a browser window and ask for permission to access your Trello account
-	Once you approve the permissions a token will be displayed, copy this to your clipboard
-	Open Alfred and type the following into the Alfred query box:
	-	**BELOW IS NOT YET IMPLEMENTED.** `Edit the workflow after it's added to Alfred to add token and board ID`
	-	**trello.token** *`<pasted_token_goes_here>`*
	-	**trello.board** (scroll the autocomplete options, once the board you would like new cards to be added to is highlighted, press enter to save)
	-	**trello.list** (scroll the autocomplete options, once the list you would like new cards to be added to is highlighted, press enter to save)

Installation & Getting Started
-------------------------------

-	trello `<card_title>`
	-	*Using Optional Description:* trello `<card_title>`**;**`<card_description>`
