# SelfHelp plugin - survey-js

This is a SelfHelpPlugin that is used for [Rserve](https://www.rforge.net/Rserve/) integration


# Installation

 - Download the code into the `plugin` folder
 - Checkout the latest version 
 - Execute all `.sql` script in the DB folder in their version order
 - If there is a survey with upload files, the files are store in the DB. Then it should be adjusted the size `max_allowed_packet` in MYSQL .ini file. The default one is 1MB

# Requirements

 - SelfHelp v6.2.1+
 - Rserve -server
