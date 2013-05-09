#!/bin/sh -e
echo "do sudo stuff"
sudo rm app/cache/* -rf
sudo chmod 777 app/cache -R 
sudo chmod 777 app/logs -R
#sudo chmod 777 app/testMails.spool -R
#sudo chmod 777 vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache -R

