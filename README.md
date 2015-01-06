Trello for Alfred v0.8
=============================

<!--![Trello Workflow for Alfred Icon](http://files.dtb.me.s3.amazonaws.com/public/trello-alfred/icon.png)-->
<img src="http://files.dtb.me/public/trello-alfred/screenshot-1.png" style="width: 600px;"/>
<img src="http://files.dtb.me/public/trello-alfred/screenshot-2.png" style="width: 300px; vertical-align: top"/>
<img src="http://files.dtb.me/public/trello-alfred/screenshot-3.png" style="width: 300px; vertical-align: top"/>

**Description:** Trello Workflow for the Mac OS app **Alfred v2** that *allows you to quickly create new cards with labels* on your **Trello** board lists.

**Version:** 0.8

Installation & Setup
-------------------------------

-	Double click on the trello.alfredworkfow file to have Alfred v2 automatically install this workflow
-	Open Alfred and enter the trigger: **trello**
	-	Select **Authorize Trello**
	-	This will open a browser window and ask for permission to access your Trello account
-	Once you approve the permissions a token will be displayed, *copy this to your clipboard*
-	Open Alfred and enter the trigger: **trello** again
	-	Select **Setup Trello**
	-	Follow the on screen instructions:
		-	Paste your Trello authorization token
		-	Enter in your Trello username
		-	Select which Trello Board you would like to use
		-	Select which list on your board you would like cards to be added to
		-	Save!
-	To change your Trello settings once they are already set, you can access the *Authorize* and *Setup* options with:
	-	`trello:auth`
	-	`trello:setup`

Usage
-------------------------------

-	trello `card_title` `;` `description` `#label` `#another-label`
	-	**Required**:
		-	`card_title`
	-	**Optional**:
		-	`description`
			-	To end the card title and start typing the description, use the `;` character
		-	`#labels`
			-	To add labels to the card, simple prefix the label name with a `#` character
	-	**Example Usage:**
		-	`trello This is my Card's title; This is my card's description... #myLabel #myOtherLabel`

Credits
-------------------------------

This workflow is based on this other [Trello Workflow](https://github.com/MikoMagni/Alfred-for-Trello) from [MikoMagni](https://github.com/MikoMagni/). [Trello API wrapper](https://github.com/ashwinks/Trello-API-PHP-Wrapper) and [PHP INI Builder](https://github.com/donatj/PhpIniBuilder) are also used.