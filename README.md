# BimBeau MultiSteps

BimBeau MultiSteps is a WordPress plugin used on the Secret Déco website to display the multi‑step enquiry form. The form is built with Elementor and the plugin exposes the necessary logic and settings to drive it.

## Installation

1. Copy the plugin directory `wp-content/plugins/bimbeau-multisteps` into your site's `wp-content/plugins` folder.
2. Ensure the **Elementor** plugin is installed and activated.
3. Activate *BimBeau MultiSteps* from the WordPress Plugins screen.

## Stripe configuration

The plugin needs Stripe secret keys to create payment links. Keys can be entered on the plugin settings page or provided as environment variables:

* `BIMBEAU_MS_SECRET_KEY` – production key
* `BIMBEAU_MS_SECRET_KEY_TEST` – test key

If the keys are not configured the plugin falls back to empty values.

## Directory layout

```
wp-content/plugins/bimbeau-multisteps/
├── assets       # CSS, JS and images
├── bimbeau-multisteps.php  # main plugin file
└── includes
    ├── admin    # settings pages
    ├── forms    # form handling logic
    └── utils    # helper functions
```
