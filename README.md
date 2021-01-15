# Vinou Connector (TYPO3 Extension)

The Vinou Connector is a TYPO3 Extension that provides a bunch of functionality based on the data from the Vinou Platform.

### Table of contents

- [What is Vinou](#what-is-vinou)
- [Features](#which-features-are-provided)
- [Requirements](#requirements)
- [Installation (recommended)](#short-installation)
- [Provider](#provider)


### What is Vinou?

Vinou is an ecosystem of applications based on the Vinou Platform. The main application is Vinou-Office that is nearly an ERP-SaaS-System for winemakers and winetraders. The goal is to automate all processes along the production and supply chain by using platform technology. We want to ensure that winemakers and winetraders can use the Vinou ecosystem as their personal tool belt in production and marketing. And this extension is a small application to transfer wine data from production to consumer automated and directly in realtime without the need of any synchronisation.

### Which features are provided?

- List and detail view for public or shopable wines
- List and detail view for public or shopable products
- List and detail view for public or shopable bundles
- A special shop list of articles
- Seperated cluster view for shop list
- Shopping card (Basket)
- **Nice Ajax-Basket handling** (To see similar functionality look here http://demoshop.vinou.de/)
- Checkout Views (Payment is directly handled within the Vinou-Platform)
- Simple enquiry form if no shop is needed
- Registration form to generate new Vinou-Member account
- Small caching system to cache api data like documents or images within your TYPO3 Installation

**All views are configurable and styleable via Fluid-Template and TypoScript**

### Requirements

- TYPO3 8.7 **in Composer Mode**
- PHP 7.2
- Vinou-Office license (Register here https://app.vinou.de/register more information can be found here https://www.vinou.de/produkte/vinou-office.html)

### Short Installation

**Please note that we currently starting Open-Source support of our extension. We try to continue completion of documentation**

####1. Install Extension

To install the extension composer is recommended

```composer require vinou/vinou-connector```

####2. Setup connection to Vinou-Platform

To connect your TYPO3 installation with your Vinou-Office log into your Vinou-Office (https://app.vinou.de) choose "connections" and create a new manual connection. This connection provides your "Token".

Next step is to fetch your AuthId from your account data. Therefore switch to settings an open account/invoice information.

At Least fill in this token and AuthId in the Extension configuration within the TYPO3-Extension-Manager

Now you're done and can start integration. Good luck and have fun.

## Provider

This Library is developed by the Vinou GmbH.

![](http://static.vinou.io/brand/logo/red.svg)

Vinou GmbH<br> 
Mombacher Straße 68<br>
55122 Mainz<br>
E-Mail: [kontakt@vinou.de](mailto:kontakt@vinou.de)<br>
Phone: [+49 6131 6245390](tel:+4961316245390)
