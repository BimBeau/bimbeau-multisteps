# BimBeau MultiSteps

This plugin powers the multi-step multi step form for Secret Déco.

## Stripe configuration

You must provide your Stripe secret keys for the plugin to operate. Keys can be
set through the plugin settings page or by defining the environment variables
`BIMBEAU_MS_SECRET_KEY` for production and `BIMBEAU_MS_SECRET_KEY_TEST` for the
test mode. If no keys are configured the plugin will fall back to empty values.
