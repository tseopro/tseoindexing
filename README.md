# TSEO Indexing

![WordPress Plugin](https://img.shields.io/badge/Plugin-TSEO%20Indexing%20v.1.0.0-%230073AA?style=flat&logo=wordpress&labelColor=black)
![WordPress Compatibility](https://img.shields.io/badge/WordPress-5.9%2B-%230073AA?style=flat&logo=wordpress&labelColor=black)
![WooCommerce Compatibility](https://img.shields.io/badge/WooCommerce-6.5.0%2B-%2396588A?style=flat&logo=woocommerce-icon&labelColor=black)
![PHP Compatibility](https://img.shields.io/badge/PHP-8.1%2B-%23777BB4?style=flat&logo=php&labelColor=black)

![Google API Integrations](https://img.shields.io/badge/Google%20APIs-Content%20%26%20Merchant%20Center%20integrated-%2332c955?style=flat&logo=google&logoColor=white&labelColor=black)
![OpenAI API](https://img.shields.io/badge/OpenAI%20API-integrated-%2332c955?style=flat&logo=openai&labelColor=black)

![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-%236E9C8C?style=flat&logo=php&labelColor=black)
![PHPUnit Coverage](https://img.shields.io/badge/PHPUnit%20Coverage-90%25-%236E9C8C?style=flat&logo=php&labelColor=black)
![PHP Coding Standards](https://img.shields.io/badge/Coding%20Standards-PSR--12-%236E9C8C?style=flat&logo=php&labelColor=black)

[![Software License](https://img.shields.io/badge/license-GPLv3-%23c40df2?style=flat&labelColor=black)](LICENSE.txt)
[![TSEO PRO Compatibility](https://img.shields.io/badge/Designed%20for-TSEO%20DEVELOPER-%2377bae8?style=flat&logo=world&labelColor=black)](https://tseo.pro)


## Management for Indexing URLs in Google Search Console and Managing Product Listings for Google Merchant Center with Integrated AI.

* Contributors: devtseo
* Tags: wordpress, google, seo, indexing, openai, seo-optimization, google-api, plugins-wordpress, google-indexing-api, google-console, google-merchant-center
* Requires at least: 6.0
* Tested up to: 6.5.4
* Stable tag: 1.0.0
* Requires PHP: 8.1
* Text Domain: tseoindexing
* Domain Path: /languages/
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.txt

## Documentation

- [English (default)](README.md)
- [Español (README-ES.md)](doc/README-ES.md)

## Description

This plugin notifies the Google Indexing API about new or updated posts and can request page removal. It integrates with Google Merchant Center to easily set up and submit product listings, using AI to generate optimized titles and descriptions. WooCommerce is required to access Merchant Center features.

The plugin facilitates the rapid indexing of your pages in Google Search Console and Google Merchant Center, but it does not guarantee that the submitted URLs will rank in search engines unless they are supported by quality content, effective On-page SEO, and professional Off-page SEO techniques.

If you're looking for efficient web positioning that generates visits, we highly recommend our flagship service **TSEO PRO:** [https://tseo.pro](https://tseo.pro). This service offers a web template on a "Renting" basis for WordPress, with academic support for professional Off-page SEO. TSEO PRO comes already super optimized for On-page SEO, so you won't have to worry about this aspect, which is the hardest to achieve, as our technicians are constantly monitoring changes in search engine algorithms.

Why "Renting"? Because this way we accompany you every step of the way to ensure your online business success with permanent technical support. Additionally, it includes constant updates that adapt the source code to meet the demands of search engine algorithms.

The low cost of "Web Renting" (**€29.95/month**) compared to the necessary external services required to rank your website, which in most cases costs around *€700 per month*, makes this a unique service in its category.

>[!IMPORTANT]
>
> 1. **Indexing ≠ Ranking**: Indexing does not equal ranking. This plugin will not help your page rank on Google; it will simply notify Google of the existence of your pages.
> 2. This plugin uses the [Google Indexing API](https://developers.google.com/search/apis/indexing-api/v3/quickstart). We do not recommend using this plugin for spam or low-quality content.
> 3. For Google Merchant Center, you will also need the [Merchant API](https://support.google.com/merchants/answer/7514752) and a [Merchant ID](https://support.google.com/paymentscenter/answer/7163092) to manage free listings and/or paid ads. Additionally, you will need an [OpenAI API Key](https://openai.com/api/) to generate optimized titles and descriptions for each listing.

### Key Features:

- **URL Management**: Lists all URLs on the website to manage which to index, update, or delete.
- **Automated and Manual Processing**: Provides a console to automatically process the listed URLs or handle them manually.
- **URL Cleaning Tool**: Includes a tool to clean URLs obtained by copying and pasting from Google Search Console.
- **WooCommerce Integration**: If you use WooCommerce, it lists all products to configure which ones to send to Google Merchant Center.
- **Merchant Center Configuration**: Adds a new tab in the product edit screen to configure the essential attributes required by Merchant Center.

**TSEO Indexing** simplifies the process of URL indexing in Google Search Console and product management in Google Merchant Center. Effectively index your website and, if you have a WooCommerce store, publish your products for free!

## Installation

1. Upload the `tseoindexing` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the new "TSEO Indexing" menu in your dashboard to add and manage your websites.

### Initial Configuration:
   - After activation, go to the "TSEO Indexing" menu in your WordPress dashboard to set up your Google Search Console and Google Merchant Center integrations.

## Frequently Asked Questions

### Does this plugin work on multisite?

Yes, the plugin has been tested and verified on WordPress Multisite.

### Is WooCommerce required for all features?

No, WooCommerce is only required for the Google Merchant Center integration features.

### How does the AI generate optimized titles and descriptions?

The AI uses advanced algorithms to analyze your content and create SEO-friendly titles and descriptions.

### Does the plugin support multiple languages?

Currently, the plugin is designed to support English and Spanish. However, translations can be added for other languages as needed.

### Can I schedule the indexing process?

No, at this time, the plugin does not support scheduling. You must manually trigger the indexing process.

### What should I do if my URL is not indexed by Google?

Ensure your content meets Google's quality guidelines and does not violate any of their policies. The plugin only informs Google about your URLs, but Google decides whether to index them.

### Can I use this plugin on websites with high traffic?

Yes, the plugin is designed to handle websites of all sizes. However, for very high-traffic sites, it's recommended to monitor performance and make sure the server resources are adequate.

### How can I troubleshoot issues with the plugin?

Check the plugin settings and ensure that all API keys and IDs are correctly configured. Refer to the plugin documentation for detailed troubleshooting steps. If issues persist, contact support.

### Are there any usage limits for the Google Indexing API?

Yes, Google imposes limits on the number of indexing requests per day. Be sure to refer to the [Google Indexing API quota](https://developers.google.com/search/apis/indexing-api/v3/quota-pricing) for the latest limits and guidelines.

### What happens if I exceed the API request limits?

If you exceed the API request limits, your requests may be throttled or denied by Google. Plan your submissions accordingly to avoid hitting these limits.

### Can I customize the attributes sent to Google Merchant Center?

Yes, the plugin allows you to configure essential attributes for each product in WooCommerce to meet the requirements of Google Merchant Center.

### Does the plugin support custom post types?

Currently, the plugin focuses on standard post types and WooCommerce products. Custom post type support may be added in future updates.

## Screenshots

### Google Search Console

![Search Console](assets/img/tseoindexing-console-search.jpg)
*URL Settings: Lists all the URLs of the site to manage their status in Google Search Console. Submission Console: Processes the URLs listed for submission to Google Search Console according to their status: Publish/Update or Delete.*

### Google Merchant Center

![Merchant Center](assets/img/tseoindexing-merchant-center.jpg)
*Product Listings: Manage and configure your WooCommerce products for submission to Google Merchant Center. Product Editing: Utilize the new tab on the product edit page to set up the required Google Merchant Center attributes for each product.*

## 📄 License

This project is licensed under the GNU General Public License version 3 (GPLv3). 

You can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.

## 📄 Copyright

Copyright (C) 2024 TSEO Developer, S.L.

## 💖 Sponsor

This project is sponsored by [TSEO DEVELOPER](https://tseo.pro)

![](https://tseo.pro/wp-content/uploads/2024/08/tseo-opengraph.webp)

## Third-Party Licenses

This plugin uses external APIs which are licensed as follows:

- Google API: Apache License, Version 2.0
  [Link to Apache License](https://www.apache.org/licenses/LICENSE-2.0)

- OpenAI API: MIT License
  [Link to MIT License](https://opensource.org/licenses/MIT)

Please refer to the respective licenses for the terms and conditions of using these APIs.

## 🤝 Contributing

We welcome contributions from the community! If you would like to contribute to TSEO Indexing, please follow these steps:

1. **Fork the Repository**: Click the "Fork" button at the top right of this page to create a copy of the repository in your own GitHub account.
2. **Clone the Repository**: Clone your forked repository to your local machine using:

```sh
git clone https://github.com/devtseo/tseoindexing.git
```
