# Coupon Popup — PrestaShop module

A PrestaShop module that shows a popup to guest visitors, inviting them to create an account and use a discount coupon. Coupon code, discount label, delay and re-appearance period are configurable from the back office.

This is the generic, configurable version of `expopopup`, a custom module I built during my professional placement at Duyal On Line for a client's online store. The original runs in production on that store; this version removes the client-specific details and makes the coupon code, discount label, delay and re-appearance period configurable from the back office.

## Features

- Shown only to visitors who are **not logged in**, and never on the account creation page.
- Appears after a configurable **delay** (default 3 seconds).
- **Close button, "No, thanks", click on the overlay or `Esc`** close it; it then stays hidden for a configurable number of days (default 2).
- Clicking **"Sign up"** takes the visitor to the registration page and hides the popup permanently for that browser.
- Accessible markup (`role="dialog"`, `aria-modal`, labelled title and close button) and a responsive layout, including a compact one for mobile landscape.
- Texts are translatable; included translations: Spanish, French, German, Italian, Portuguese, Dutch and Greek.

## Requirements

- PrestaShop 1.7 or later. The original module was developed and tested locally on PrestaShop 1.7.8.11 (Docker) before going to production.

## Installation

1. Copy this folder into your shop's `modules/` directory and name it `couponpopup`.
2. In the back office, go to **Modules → Module Manager**, search for "Coupon popup" and click **Install**.
3. Click **Configure** to set up the popup.

## Configuration

| Setting | Default | Description |
| --- | --- | --- |
| Coupon code | `WELCOME10` | Code shown in the popup (1–32 letters, digits, `-` or `_`). |
| Discount label | `10%` | Text shown in the title and message (up to 20 characters). |
| Delay | `3` seconds | Time before the popup appears (0–60). |
| Reappear after | `2` days | Days before it is shown again to a visitor who closed it (1–365). |

> **Note:** the module only *displays* the coupon code. Create the matching cart rule yourself in **Catalog → Discounts → Cart Rules**, with the same code.

## How it works

- Registers two hooks: `displayHeader` (to load the CSS and JavaScript only when the popup applies) and `displayBeforeBodyClosingTag` (to render the Smarty template).
- Settings are stored with PrestaShop's `Configuration` class and validated on save (restricted character sets and numeric ranges), then passed to the template, which exposes the delay and re-appearance days as `data-*` attributes read by the JavaScript.
- The visitor's choice is stored in the browser's `localStorage` (`couponpopup_seen`), so it is per browser, not per customer account.

## Project structure

```
couponpopup/
├── couponpopup.php              # Module class: install, config page, hooks
├── views/
│   ├── css/couponpopup.css
│   ├── js/couponpopup.js
│   └── templates/hook/popup.tpl
└── translations/                # es, fr, de, it, pt, nl, el
```

## License

Released under the [MIT License](LICENSE).

## Author

Francisco Martín Hersog — [github.com/fcomartin94](https://github.com/fcomartin94)
