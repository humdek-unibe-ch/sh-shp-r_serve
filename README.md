# SelfHelp plugin - survey-js

This is a SelfHelpPlugin that is used for [Rserve](https://www.rforge.net/Rserve/) integration


# Installation

 - Download the code into the `plugin` folder
 - Checkout the latest version 
 - Execute all `.sql` script in the DB folder in their version order
 - If there is a survey with upload files, the files are store in the DB. Then it should be adjusted the size `max_allowed_packet` in MYSQL .ini file. The default one is 1MB

# Requirements

 - SelfHelp v6.3.0+
 - Rserve -server

# R installation
 - sudo apt-get install r-base
 - sudo apt-get install r-base-dev # required for installing packages

# Rserve Installation
# install Rserve
install.packages("Rserve")
library(Rserve)
Rserve(args="--RS-enable-remote --RS-worker-threads=8")

#start process manually
R CMD Rserve --vanilla --RS-conf /etc/Rserv.conf

# check process
ps aux | grep Rserve

# kill process
killall -INT Rserve

# install libraries for httr package
sudo apt install libcurl4-openssl-dev
sudo apt install libssl-dev
install.packages("httr")

Sudo su
instal R, instal Rserve

# create file  sudo vim /etc/Rserv.conf
auth required
remote enable
plaintext enable
pwdfile /etc/Rserve_users.txt

# create file with users sudo vim /etc/Rserve_users.txt
stefan 1234
bashev q1w2

# create service sudo vim /etc/systemd/system/rserve.service
[Unit]
Description=Rserve - R server

[Service]
Type=forking
ExecStart=/usr/bin/R CMD Rserve --vanilla --RS-conf /etc/Rserv.conf

[Install]
WantedBy=default.target

# reload the daemon sudo systemctl daemon-reload

# Install packages - use this script. The installation on Linux takes time because everything is compiled. This will happen only the first time when is installed
`install_if_missing <- function(package_name) {
  if (!require(package_name, character.only = TRUE, quietly = TRUE)) {
    install.packages(package_name, dependencies = TRUE, repos = "https://cran.r-project.org")
    library(package_name, character.only = TRUE)
  }
}

install_if_missing("tidyverse")`





