# Virtual Y Authentication plugins.

Virtual Y provide's pluginable system for authentication variants.
You could easily implement your own integration with your CRM if it doesent exist yet.
At now, we have implemented next authentication plugins:

## [Dummy](/modules/openy_gc_auth/modules/openy_gc_auth_demo/README.md)

Demo or bypass plugin. User could enter your gated area by clicking on Enter Virtual YMCA button.
Could be used for testing of your Virtual Y or for demonstration of Virtual Y features.
Virtual Y creates real Drupal user after each successful login to the system based on his/her ip address as username.

## Custom

Virtual Y user could enter gated content area by entering email address. 
For better security Recaptcha and emaill link approval features could be enabled at plugin settings form.
This module could be used for integration with the CRM's that dont have authentication plugin implemented or dont support SSO.
For example: Active.net.

## Daxko SSO

Virtual Y user could enter Virtual Y using his Daxko credentials. This module require's Daxko Engage product to be enabled at your account.

## Daxko Barcode

Lightweight check for Daxko based on user entered barcode.

## Personify 

Virtual Y user could enter Virtual Y using Personify SSO.



