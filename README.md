![South Waterfront](http://www.southwaterfront.com/images/swcr-logo.png)

# Parking Tracker API

[Android](https://github.com/WSUV-CS420-Team4/ParkingTracker)

[Web](https://github.com/WSUV-CS420-Team4/ParkingTrackerWeb)

[Docs](https://github.com/WSUV-CS420-Team4/ParkingTrackerDocs)

ParkingTracker is an application designed to help neighborhood/community groups track the usage of parking spaces
within their neighborhood. An Android device is used to collect data by inputting license plates via the system camera
and OCR. This data is then stored in a database and accessed/analyzed by a web application.

Sponsor: [South Waterfront](http://www.southwaterfront.com/)

## Component Overview

A PHP REST API using Slim. It is used to collect data from the ParkingTracker Android application and to provide
a backend for the analysis web application. The API will also handle authentication via Oauth2.

## Authors

- Joel
- Vito
- Jason
- Bob

## Status

Currently accepts uploaded data and will provide lists of blockface data.

## Install

- Upload files
- Run composer
- Install database schema
- Configure

## Configuration

Copy config.example.php to config.php and replace the example values with the correct values for your database.

## Known Bugs & Caveats

- None so far as we're aware

## To Do

- Analysis of parking usage
- Spreadsheet creation
