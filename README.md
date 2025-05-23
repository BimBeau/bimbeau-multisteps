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

### Admin navigation

Each administration page prints a placeholder for the navigation tabs.  The
script `assets/js/admin-tabs.js` replaces this markup with Gutenberg’s
`TabPanel` component so you can switch between dashboard pages with a single
click.

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

## Multi-step form

The enquiry form itself is embedded with the `[multi_step_form]` shortcode.
Use the optional `etape` attribute to display a specific step:

```text
[multi_step_form etape="1"]  # shows the first step
[multi_step_form etape="2"]  # shows the second step
```

Each step should be placed on its own page. When a user submits the form, the
plugin records the answers and automatically redirects to the next step.

## Menu customization

The advanced settings page lets you change the label and Dashicon used for the plugin's admin menu. Update these fields and save the options to see the new menu name and icon.
