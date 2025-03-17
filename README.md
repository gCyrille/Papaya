# Papaya
Papaya is software to help into the management of vegetables deliveries. 

Initially developed for a non-profit organization based in Chikowa, Zambia: the CYDC, Chikowa Youth Development Centre. 
This project is now open-source under MIT license to help similar projects or organizations, but also to allow everybody to improve this software.

Ce projet a pour but d'aider la ferme à organiser ses livraisons et les commandes des clients.
Il a été développé en PHP/HTML5 dans l'optique de facilité son évolution dans le temps malgré le changement de volontaires, car c'est un couple de langage connu et relativement facile à appréhender.

Ce logiciel utilise les framework Codeigniter et Semantic-UI pour la partie backend et la partie front-end. 
Pour la partie base de donnée il se repose sur une base MySQL standard. 
Tout a été fait pour faciliter son installation sur un poste utilisateur hors-ligne, nécessitant uniquement une installation de XAMPP.

### Version 2.0

This version only add support for PHP >=8.2. All features are the same as Papaya 1.8

## Migration to PHP >=8.2

The project now uses https://github.com/pocketarc/codeigniter to work with PHP >=8.2.

## PDFtoPrinter

In order to print invoces and others files, the application uses: https://mendelson.org/pdftoprinter.html  
Refer to the documentation if you have any issue to print.

## Installation

Go to http://127.0.0.1/papaya/install and follow the instructions.