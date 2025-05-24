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

## Email placeholders

When editing email templates or labels you can use the following placeholders:

* `{prenom}` – client's first name
* `{nom}` – client's last name
* `{date}` – chosen reply date
* `{details}` – summary of the request

These tags will be replaced with the relevant values when emails are sent.

## Data Forms and Data Views

The plugin exposes two shortcodes to manage step data from the front‑end:

* `[ms_data_form]` – displays a form to create or edit a step definition. Use
  `[ms_data_form id="123"]` to edit a specific step.
* `[ms_data_view]` – lists the existing steps in a searchable table.

Both shortcodes are useful to build custom management pages without accessing
the WordPress administration directly.
